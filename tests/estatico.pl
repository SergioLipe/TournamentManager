#!/usr/bin/perl
# Rough sanity checks for the Tournament project.
# Not a parser: strips comments/strings, then checks bracket balance.
use strict;
use warnings;
use File::Find;
use File::Basename;
use File::Spec;

my $ROOT = $ENV{'PROJ'} || '/c/Claude/TournamentManager';
my @problems;

# --- strip comments and string literals -------------------------------------
sub strip {
    # $css: CSS has no '#' line comments and '#' starts hex colours / id
    # selectors, so treating it as one would swallow the following '{'.
    my ($src, $css) = @_;
    my $out = '';
    my $i = 0;
    my $n = length $src;
    while ($i < $n) {
        my $c   = substr($src, $i, 1);
        my $two = substr($src, $i, 2);
        if ((!$css and $two eq '//') or (!$css and $c eq '#')) {
            my $j = index($src, "\n", $i);
            $i = ($j == -1) ? $n : $j;
            next;
        }
        if ($two eq '/*') {
            my $j = index($src, '*/', $i + 2);
            $i = ($j == -1) ? $n : $j + 2;
            next;
        }
        if ($c eq "'" or $c eq '"') {
            my $q = $c;
            $i++;
            while ($i < $n) {
                my $d = substr($src, $i, 1);
                if ($d eq "\\") { $i += 2; next; }
                if ($d eq $q)   { $i++; last; }
                $i++;
            }
            $out .= '""';
            next;
        }
        $out .= $c;
        $i++;
    }
    return $out;
}

# --- bracket balance --------------------------------------------------------
sub balance {
    my ($src, $pairs) = @_;
    $pairs ||= '(){}[]';
    my %close;
    for (my $k = 0; $k < length($pairs); $k += 2) {
        $close{ substr($pairs, $k + 1, 1) } = substr($pairs, $k, 1);
    }
    my %is_open = map { $_ => 1 } values %close;

    my @stack;
    my $line = 1;
    for my $ch (split //, $src) {
        if    ($ch eq "\n")     { $line++ }
        elsif ($is_open{$ch})   { push @stack, [$ch, $line] }
        elsif ($close{$ch}) {
            return "unexpected '$ch' at line $line" unless @stack;
            my $top = pop @stack;
            return "'$top->[0]' from line $top->[1] closed by '$ch' at line $line"
                if $top->[0] ne $close{$ch};
        }
    }
    if (@stack) {
        return "unclosed '$stack[-1][0]' opened at line $stack[-1][1]";
    }
    return undef;
}

sub slurp {
    my ($p) = @_;
    open my $fh, '<', $p or die "cannot read $p: $!";
    local $/;
    my $c = <$fh>;
    close $fh;
    return $c;
}

# --- collect php files ------------------------------------------------------
my @php;
find(sub {
    # backup-live-* são cópias do site antigo descarregadas antes de publicar:
    # têm mesmo o código velho lá dentro, e não é isso que se está a verificar.
    if (-d $_ and ($_ eq '.git' or $_ eq 'Imagens' or $_ eq 'vendor'
                   or $_ =~ /^backup-live-/)) {
        $File::Find::prune = 1;
        return;
    }
    push @php, $File::Find::name if /\.php$/;
}, $ROOT);

printf "== PHP bracket balance (%d files) ==\n", scalar @php;
for my $p (sort @php) {
    my $src = slurp($p);
    my $rel = $p; $rel =~ s/^\Q$ROOT\E.//;

    my $code = '';
    $code .= $1 while $src =~ /<\?php(.*?)(?:\?>|\z)/gs;

    my $err = balance(strip($code));
    if ($err) { print "  FAIL $rel: $err\n"; push @problems, "$rel: $err"; }

    unless ($src =~ /<\?php/) {
        print "  FAIL $rel: no opening PHP tag\n";
        push @problems, "$rel: no opening tag";
    }
}
print "  all balanced\n" unless @problems;

print "\n== JS bracket balance ==\n";
# O sw.js tem de estar na raiz — um service worker só controla a pasta onde
# está e as de baixo — por isso não é apanhado pelo glob do JavaScript/.
for my $fn (sort(glob("$ROOT/JavaScript/*.js"), glob("$ROOT/sw.js"))) {
    my $err = balance(strip(slurp($fn)));
    printf "  %-5s %s%s\n", ($err ? 'FAIL' : 'ok'), basename($fn), ($err ? ": $err" : '');
    push @problems, basename($fn) . ": $err" if $err;
}

print "\n== CSS brace balance ==\n";
{
    my $err = balance(strip(slurp("$ROOT/CSS/style.css"), 1), '{}');
    printf "  %-5s style.css%s\n", ($err ? 'FAIL' : 'ok'), ($err ? ": $err" : '');
    push @problems, "style.css: $err" if $err;
}

# --- referenced paths -------------------------------------------------------
print "\n== referenced paths resolve ==\n";
my %missing;
for my $p (@php) {
    my $src = slurp($p);
    my $dir = dirname($p);
    my $rel = $p; $rel =~ s/^\Q$ROOT\E.//;

    while ($src =~ /require(?:_once)?\s+__DIR__\s*\.\s*'([^']+)'/g) {
        my $t = File::Spec->canonpath("$dir/$1");
        $missing{"$rel -> $1"} = 1 unless -e $t;
    }
    while ($src =~ /(?:href|src)="(?!https?:|mailto:|#)([^"?<]+)"/g) {
        my $t = "$ROOT/$1";
        $missing{"$rel -> $1"} = 1 unless -e $t;
    }
    while ($src =~ /'(JavaScript\/[\w.]+)'/g) {
        $missing{"$rel -> $1"} = 1 unless -e "$ROOT/$1";
    }
}
for my $fn (glob("$ROOT/JavaScript/*.js")) {
    my $src = slurp($fn);
    while ($src =~ /fetch\('([^']+)'/g) {
        $missing{ basename($fn) . " -> $1" } = 1 unless -e "$ROOT/$1";
    }
}
if (%missing) {
    print "  MISSING $_\n" for sort keys %missing;
    push @problems, keys %missing;
} else {
    print "  all referenced paths resolve\n";
}

# --- leftovers from the old version ----------------------------------------
print "\n== leftover references to deleted code ==\n";
my @dead = qw(funcoesBD navbar.php style2.css Brackets.js Carregar_Imagens
              UpdateEstatistica _Brackets.html obterDadosBaseDados
              guardarDadosBaseDados ativarLigacaoBaseDados);
my $found = 0;
find(sub {
    if (-d $_ and ($_ eq '.git' or $_ eq 'Imagens' or $_ eq 'vendor' or $_ eq 'database'
                   or $_ =~ /^backup-live-/)) {
        $File::Find::prune = 1;
        return;
    }
    return unless /\.(php|js|css|md)$/;
    my $src = slurp($File::Find::name);
    my $rel = $File::Find::name; $rel =~ s/^\Q$ROOT\E.//;
    # Comentários e prose podem mencionar o que foi removido de propósito;
    # o que interessa é não sobrar nenhuma referência executável.
    $src =~ s{//[^\n]*}{}g;
    $src =~ s{/\*.*?\*/}{}gs;
    $src =~ s{^\s*(?:>|-|\d+\.)?\s*[^\n]*_Brackets\.html[^\n]*$}{}gm if $rel =~ /\.md$/;
    for my $d (@dead) {
        if (index($src, $d) >= 0) {
            print "  FOUND '$d' in $rel\n";
            push @problems, "dead ref $d in $rel";
            $found = 1;
        }
    }
}, $ROOT);
print "  none\n" unless $found;

print "\n", (@problems ? "FAILURES: " . scalar(@problems) : "All checks passed."), "\n";
exit(@problems ? 1 : 0);
