<?php

namespace App\Models;

require_once '../vendor/autoload.php';

use Illuminate\Support\Facades\Config;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector as WP;
use Mike42\Escpos\Printer as P;

class ThermalReceipt
{

    private function getSpaceSettings()
    {
        return [
            'QTY' => 5,
            'DESCRIPTION' => 34,
            'EXPIRY' => 7,
            'PRICE' => 10,
        ];
    }

    public function generate_print($receipt_info)
    {
        try {

            $currency = Config::get('constant.currency') . ' ';

            $connector = new WP($receipt_info['printer_name']);
            $printer = new P($connector);

            $printer->setEmphasis(true);
            $printer->setJustification(P::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text($receipt_info['branch_name']);
            $printer->text("\n");
            $printer->feed();

            $printer->setEmphasis(false);
            $printer->setJustification(P::JUSTIFY_LEFT);

            $printer->selectPrintMode();
            $printer->setTextSize(1, 1);
            $printer->text($receipt_info['branch_code']);
            $printer->text("\n");
            $printer->text($receipt_info['address']);
            $printer->text("\n");
            $printer->text('License no : ' . $receipt_info['license_no']);
            $printer->text("\n");
            $printer->text('Tel : ' . $receipt_info['telephone']);
            $printer->text("\n");

            $printer->text('Receipt: #' . $receipt_info['receipt_no']);
            $printer->text("\n");
            $printer->text('Type: ' . $receipt_info['type']);
            $printer->text("\n");

            $printer->feed();
            $printer->selectPrintMode(P::MODE_DOUBLE_HEIGHT | P::MODE_FONT_A | P::MODE_DOUBLE_WIDTH);
            $printer->setJustification(P::JUSTIFY_LEFT);
            $printer->text($receipt_info['customer']);
            $printer->text("\n");

            $printer->setJustification(P::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->text('Tel : ' . $receipt_info['customer_tel']);
            $printer->text("\n");

            $printer->selectPrintMode();
            $printer->setEmphasis(false);
            $printer->setJustification(P::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->text('Date/Time : ' . $receipt_info['date_time']);
            $printer->text("\n");
            $printer->text('Associate : ' . $receipt_info['emp_id']);
            $printer->text("\n");

            $printer->setJustification(P::JUSTIFY_CENTER);
            $printer->feed();

            $printer->setUnderline(P::UNDERLINE_DOUBLE);
            $printer->setFont(P::FONT_B);

            foreach ($this->getSpaceSettings() as $attr => $space) {
                $printer->text(str_pad(ucwords($attr), $space, ' ', $space == 'total' ? STR_PAD_LEFT : STR_PAD_RIGHT));
            }

            $printer->setUnderline(P::UNDERLINE_NONE);
            $printer->text("\n");
            $printer->setFont(P::FONT_B);
            $printer->setTextSize(1, 1);

            if ($receipt_info['items'] != null) {

                foreach ($receipt_info['items'] as $single_item) {

                    $string1 = $single_item->item_name;

                    $qty = $single_item->unit;

                    $total = $single_item->selling_price;

                    $string = (strlen($string1) > 34) ? substr($string1, 0, 34) . '...' : $string1;

                    $printer->setTextSize(1, 2);

                    $printer->setReverseColors(true);

                    $printer->text($this->getPad(' ' . $qty . 'x', 'QTY'));

                    $printer->text($this->getPad($string, 'DESCRIPTION'));

                    $printer->text($this->getPad(date('M-y', strtotime($single_item['expiry_date'])), 'EXPIRY'));

                    $printer->text($this->getPad($this->formatAmount($total), 'PRICE'));

                    $printer->setReverseColors(false);

                    $printer->feed(2);

                    $printer->setTextSize(1, 1);
                    $printer->setJustification(P::JUSTIFY_LEFT);

                    $printer->text('Mode: ' . $single_item['mode'] . ' Batch: ' . $single_item['batch_no'] . ' Pack Size: ' . $single_item['pack_size'] . ' Total: ' . $single_item['sub_total']);
                    $printer->text("\n");

                }
            }

            $printer->setFont(P::FONT_A);
            $printer->setTextSize(1, 1);
            $printer->text("------------------------------------------\n");
            $printer->feed();

            $printer->setJustification(P::JUSTIFY_LEFT);

            if ($receipt_info['description'] != '') {
                $printer->setEmphasis(true);
                $printer->text('Memo : ' . $receipt_info['description']);
                $printer->setEmphasis(false);
                $printer->text("\n");
            }

            if ($receipt_info['doctor_details'] != '') {
                $printer->setEmphasis(true);
                $printer->text('Doc Info : ' . $receipt_info['doctor_details']);
                $printer->setEmphasis(false);
                $printer->text("\n");
            }

            if ($receipt_info['patient_details'] != '') {
                $printer->setEmphasis(true);
                $printer->text('Patient Info : ' . $receipt_info['patient_details']);
                $printer->setEmphasis(false);
                $printer->text("\n");
            }

            $printer->feed();

            $printer->setJustification(P::JUSTIFY_RIGHT);

            $subtotal = $receipt_info['sub_total'];

            $printer->text('Sub Total ' . $currency . $this->formatAmount($subtotal));
            $printer->text("\n");

            if ($receipt_info['total_discount'] != 0) {
                $printer->text('Total Disc ' . $currency . $receipt_info['total_discount']);
                $printer->text("\n");
            }

            $printer->text('Total Tax ' . $currency . $this->formatAmount($receipt_info['total_tax']));
            $printer->text("\n");

            $printer->selectPrintMode(P::MODE_DOUBLE_HEIGHT);
            $printer->feed();
            $printer->text('Total ' . $currency . $this->formatAmount($receipt_info['total']));
            $printer->text("\n");
            $printer->feed();

            $printer->setTextSize(1, 1);
            $printer->setJustification(P::JUSTIFY_CENTER);
            $printer->text('Transaction details: ');
            $printer->text("\n");

            $printer->text("******************************************\n");

            $printer->setJustification(P::JUSTIFY_LEFT);

            if (count($receipt_info['invoice_payment']) > 0) {

                foreach ($receipt_info['invoice_payment'] as $payments) {

                    $printer->text('Tender Type : ' . $payments->payment_type);
                    $printer->text("\n");
                    $printer->text('Amount : ' . $currency . $this->formatAmount($payments->trans_total_amount));
                    $printer->text("\n");
                }
            }

            $printer->setJustification(P::JUSTIFY_RIGHT);
            $printer->setTextSize(1, 1);
            $printer->feed();
            $printer->text('Total Tendered : ' . $currency . $this->formatAmount($receipt_info['tendered']));
            $printer->text("\n");

            $printer->text('Total Change : ' . $currency . $this->formatAmount($receipt_info['change']));
            $printer->text("\n");
            $printer->feed();

            $printer->setJustification(P::JUSTIFY_CENTER);
            $printer->selectPrintMode(P::MODE_FONT_A);
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(true);

            if (count($receipt_info['statements']) > 0 and $receipt_info['statements'] != null) {
                foreach ($receipt_info['statements'] as $single_states) {
                    if ($single_states->receipt_heading != null) {
                        $printer->text($single_states->receipt_heading . " \n");
                    }

                    $printer->text($single_states->receipt_content . " \n");

                    $printer->text("******************************************\n");
                }
            }

            $printer->feed();
            $printer->text("***  Software By Spantik Lab  ***\n");
            $printer->text("\n");

            $printer->cut();

            $printer->close();

            $printer_result = 'success';
        } catch (Exception $e) {
            $printer_result = 'failed';
        }

        return $printer_result;
    }

    public function getPad($str, $attr)
    {
        return str_pad($str, $this->getSpaceSettings()[$attr], ' ', $attr == 'total' ? STR_PAD_LEFT : STR_PAD_RIGHT);
    }

    public function formatAmount($n)
    {
        return number_format($n, 2, '.', '');
    }
}