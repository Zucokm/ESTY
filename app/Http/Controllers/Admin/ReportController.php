<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function exportOrders(Request $request)
    {
        $orders = Order::with('user')->latest()->get();

        $fileName = 'esty_sales_report_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Order ID', 'Customer Name', 'Email', 'Total Amount (Ks)', 'Payment Method', 'Status', 'Township', 'Shipping Address', 'Phone', 'Date Placed'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row['Order ID']  = 'VR-' . $order->id;
                $row['Customer Name'] = $order->user ? $order->user->name : 'Guest';
                $row['Email']    = $order->user ? $order->user->email : '-';
                $row['Total Amount']  = $order->total_amount;
                $row['Payment Method']  = $order->payment_method;
                $row['Status']  = $order->status;
                $row['Township']  = $order->township;

                $row['Shipping Address']  = $order->shipping_address;
                $row['Phone']  = $order->phone;
                $row['Date Placed']  = $order->created_at->format('Y-m-d H:i:s');

                fputcsv($file, array($row['Order ID'], $row['Customer Name'], $row['Email'], $row['Total Amount'], $row['Payment Method'], $row['Status'], $row['Township'], $row['Shipping Address'], $row['Phone'], $row['Date Placed']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
