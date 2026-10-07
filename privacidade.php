<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

/**
 * Política de privacidade.
 *
 * O Google Play exige um endereço público com a política de privacidade da
 * app. Está em inglês, como o resto do site. O que aqui se diz tem de bater
 * com o que o código faz: se um dia o site passar a guardar mais alguma
 * coisa, esta página muda com ele (e o formulário "Segurança dos dados" na
 * Play Console também).
 */

$tituloPagina = 'Privacy policy';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-largo">
    <h1 class="h2 mb-1">Privacy policy</h1>
    <p class="text-muted mb-4">Tournament (torneio.site and the Android app) · Last updated 7 October 2026</p>

    <div class="card sobre">
        <div class="card-body privacidade">
            <h2 class="h5">In short</h2>
            <p>
                Tournament has no accounts and does not ask for, collect or sell any
                personal information. There are no ads, no analytics and no trackers.
            </p>

            <h2 class="h5">What is stored on our server</h2>
            <p>
                When you finish a tournament with one of the built-in themes, the app
                sends which competitor beat which. These results are added to the
                shared totals shown on the Statistics page. They are anonymous: they
                contain no name, account, device identifier or location, and cannot be
                traced back to you.
            </p>
            <p>
                Tournaments played with your own list of names are never sent
                anywhere. The names stay on your device.
            </p>

            <h2 class="h5">Cookies and data on your device</h2>
            <ul>
                <li>
                    One session cookie, used only as a security check so that results
                    can only be sent from this app. It holds no personal information
                    and is deleted when you close the app or browser.
                </li>
                <li>
                    Two preferences kept on your device: the bracket size you chose
                    (8 or 16) and whether the top bar is hidden. They never leave
                    your device.
                </li>
            </ul>

            <h2 class="h5">Other services</h2>
            <p>
                Like any website, our hosting provider (iFastNet) keeps standard
                server logs, such as IP address, browser type and the pages
                requested, to keep the service running and secure. Part of the page
                styling is loaded from the jsDelivr content delivery network, which
                receives the same kind of technical information when it sends those
                files. We do not use these logs to identify anyone.
            </p>

            <h2 class="h5">Children</h2>
            <p>
                Tournament does not knowingly collect personal information from anyone,
                including children, because it does not collect personal information
                at all.
            </p>

            <h2 class="h5">Changes</h2>
            <p>
                If this policy changes, the new version will be published on this page
                with a new date.
            </p>

            <h2 class="h5">Contact</h2>
            <p class="mb-0">
                Sérgio Filipe Azevedo Gonçalves<br>
                <a href="mailto:lipewtf@hotmail.com">lipewtf@hotmail.com</a>
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
