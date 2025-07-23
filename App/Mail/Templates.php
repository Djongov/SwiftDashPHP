<?php

declare(strict_types=1);

namespace App\Mail;

use App\Mail\Send;
use App\Logs\SystemLog;

class Templates
{
    public static function informAdministrator(string $subject, string $message): void
    {
        $html = '<p><b>Hello ' . ADMINISTRATOR_EMAIL . ',</b></p>';
        $html .= '<p>' . $message . '</p>';
        $html .= '<p>Best regards,</p>';
        $html .= '<p>' . translate('site_title') . '</p>';
        $html .= '<p><small>This is an automated message. Please do not reply.</small></p>';
        $html .= '<p><small>Version: ' . VERSION . '</small></p>';
        try {
            Send::send(
                [
                [
                    'email' => ADMINISTRATOR_EMAIL
                ]
                ],
                $subject,
                $html
            );
        } catch (\Exception $e) {
            SystemLog::write('Mail', 'Error sending email to ' . ADMINISTRATOR_EMAIL . ': ' . $e->getMessage(), 'informAdministrator');
        }
    }
    public static function lowestPriceAlertHtml(string $user, int $internalId, string $store, array $data, $lang = DEFAULT_LANG): string
    {
        $stockColorGood = 'green';
        $stockColorWarning = 'orange';
        $stockColorBad = 'red';

        // If bad
        if (in_array($data['stock'], STOCK_BAD_VALUES) || in_array($data['stock_text'], STOCK_TEXT_BAD_VALUES)) {
            $stockAddition = '<p>' . translate('stock_out_of_stock_message', [], $lang) . '<span style="color:' . $stockColorBad . ';"><b>' . $data['stock_text'] . '</b></span></p>';
        }
        // If warning
        if (in_array($data['stock'], STOCK_WARNING_VALUES) || in_array($data['stock_text'], STOCK_TEXT_WARNING_VALUES)) {
            $stockAddition = '<p>' . translate('stock_limited_qty_message', [], $lang) . '<span style="color:' . $stockColorWarning . ';"><b>' . $data['stock_text'] . '</b></span></p>';
        }
        // If good
        if (in_array($data['stock'], STOCK_GOOD_VALUES) || in_array($data['stock_text'], STOCK_TEXT_GOOD_VALUES)) {
            $stockAddition = '<p>' . translate('stock_in_stock_message', [], $lang) . '<span style="color:' . $stockColorGood . ';"><b>' . $data['stock_text'] . '</b></span></p>';
        }
        $html = '<h4>' . translate('dear', [], $lang) . $user . ',</h4>';
        $html .= '<p><img height="128" width="128" src="' . $data['image'] . '" alt="' . $data['title'] . '" /></p>';
        $html .= '<p>' . translate('the_product', [], $lang) . '<b>' . $data['title'] . '</b>' . translate('has_a_new_lowest_price_of', [], $lang) . '<span style="color:red">' . $data['price'] . ' ' . convertCurrency($data['currency'], [], $lang) . '</span> / <span style="color:red">' . $data['price_eur'] . ' €</span>.</p>';
        $html .= $stockAddition;
        if ($store === 'emag') {
            $html .= '<p>' . translate('vendor', [], $lang) . ': <b>' . $data['vendor'] . '</b></p>';
        }
        $html .= '<p>' . translate('go_check_it_out_on', [], $lang) . '<a href="https://' . $_SERVER['HTTP_HOST'] . '/products/' . $store . '/' . $internalId . '">' . translate('site_title') . '</a>' . translate('or_directly_at', [], $lang) . '<a href="' . $data['callingMetadata']['final_url'] . '">' . ucfirst($store) . '</a></p>';
        $html .= '<p>' . translate('best_regards', [], $lang) . '</p>';
        $html .= '<p>' . translate('site_title') . '</p>';
        $html .= '<p><small>' . translate('this_is_an_automated_message', [], $lang) . '</small></p>';
        $html .= '<p><small>' . translate('version', [], $lang) . ': ' . VERSION . '</small></p>';
        return $html;
    }
}
