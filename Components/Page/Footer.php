<?php

declare(strict_types=1);

namespace Components\Page;

class Footer
{
    public static function render($theme): string
    {
        $html = '';
        $currentYear = date('Y');
        $footerClass = 'mt-auto ' . LIGHT_COLOR_SCHEME_CLASS . ' ' . DARK_COLOR_SCHEME_CLASS;
        $html .= '<footer class="' . $footerClass . '">';
            $html .= '<div class="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between">';
                $html .= '<span class="text-sm sm:text-center ' . TEXT_COLOR_SCHEME . ' ' . TEXT_DARK_COLOR_SCHEME . '">© ' . $currentYear . ' <a href="/" class="hover:underline">' . SITE_TITLE . '</a> '  . VERSION . ' - ' . translate('all_rights_reserved') . '</span>';
                $html .= '<ul class="flex flex-wrap justify-center items-center mt-3 text-sm font-medium sm:mt-0">';
                    $html .= '<li>';
                        $html .= '<a id="cookie-consent-link" href="" class="hover:underline me-4 md:me-6">' . translate('cookie_policy') . '</a>';
                    $html .= '</li>';
                    $html .= '<li>';
                        $html .= '<a href="/privacy-policy" class="hover:underline me-4 md:me-6">' . translate('privacy_policy') . '</a>';
                    $html .= '</li>';
                    $html .= '<li>';
                        $html .= '<a href="/terms-of-service" class="hover:underline me-4 md:me-6">' . translate('terms_of_service') . '</a>';
                    $html .= '</li>';
                    $html .= '<li>';
                        $html .= '<a href="https://github.com/Djongov/SwiftDashPHP" title="Source Code" class="hover:underline ms-4 md:ms-6 flex items-center" target="_blank">';
                            $html .= '<svg fill="currentColor" width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 24 24"><path d="M12,2.2467A10.00042,10.00042,0,0,0,8.83752,21.73419c.5.08752.6875-.21247.6875-.475,0-.23749-.01251-1.025-.01251-1.86249C7,19.85919,6.35,18.78423,6.15,18.22173A3.636,3.636,0,0,0,5.125,16.8092c-.35-.1875-.85-.65-.01251-.66248A2.00117,2.00117,0,0,1,6.65,17.17169a2.13742,2.13742,0,0,0,2.91248.825A2.10376,2.10376,0,0,1,10.2,16.65923c-2.225-.25-4.55-1.11254-4.55-4.9375a3.89187,3.89187,0,0,1,1.025-2.6875,3.59373,3.59373,0,0,1,.1-2.65s.83747-.26251,2.75,1.025a9.42747,9.42747,0,0,1,5,0c1.91248-1.3,2.75-1.025,2.75-1.025a3.59323,3.59323,0,0,1,.1,2.65,3.869,3.869,0,0,1,1.025,2.6875c0,3.83747-2.33752,4.6875-4.5625,4.9375a2.36814,2.36814,0,0,1,.675,1.85c0,1.33752-.01251,2.41248-.01251,2.75,0,.26251.1875.575.6875.475A10.0053,10.0053,0,0,0,12,2.2467Z"></path></svg>';
                        $html .= '</a>';
                    $html .= '</li>';
                $html .= '</ul>';
            $html .= '</div>';
        $html .= '</footer>';
        // Scroll to top button
        $html .= '<span data-mdb-ripple="true" data-mdb-ripple-color="light" class="cursor-pointer inline-block p-3 bg-' . $theme . '-600 text-white font-medium text-xs leading-tight uppercase rounded-full shadow-md hover:bg-' . $theme . '-700 hover:shadow-lg focus:bg-' . $theme . '-700 focus:shadow-lg focus:outline-none focus:ring-2 active:bg-' . $theme . '-800 active:shadow-lg transition duration-150 ease-in-out bottom-5 right-5 fixed hidden" id="btn-back-to-top">';
            $html .= '<svg aria-hidden="true" focusable="false" data-prefix="fas" class="w-4 h-4" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path fill="currentColor" d="M34.9 289.5l-22.2-22.2c-9.4-9.4-9.4-24.6 0-33.9L207 39c9.4-9.4 24.6-9.4 33.9 0l194.3 194.3c9.4 9.4 9.4 24.6 0 33.9L413 289.4c-9.5 9.5-25 9.3-34.3-.4L264 168.6V456c0 13.3-10.7 24-24 24h-32c-13.3 0-24-10.7-24-24V168.6L69.2 289.1c-9.3 9.8-24.8 10-34.3.4z"></path></svg>';
        $html .= '</span>';
        $language = $_SESSION['lang'] ?? DEFAULT_LANG;
        $html .= '<input type="hidden" name="lang" value="' . $language . '" />';
        $html .= '<input type="hidden" name="theme" value="' . $theme . '" />';
        return $html;
    }
}
