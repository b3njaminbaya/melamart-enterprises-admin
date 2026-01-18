<?php    
require_once 'core.php';

$orderId = $_POST['orderId'];

$sql = "SELECT order_date, expect_return_date, returned_date, site_location, client_name, client_contact, driver_name, driver_contact, returned_by, returned_by_contact, approved_by, sub_total, vat, total_amount, discount, grand_total, paid, due, payment_type, payment_status, payment_place, gstn, order_status FROM orders WHERE order_id = $orderId";

$orderResult = $connect->query($sql);
$orderData = $orderResult->fetch_array();

// Convert date strings to DateTime objects
$orderDate = new DateTime($orderData[0]);
$expectReturnDate = new DateTime($orderData[1]);
$returnedDate = ($orderData[2] != '0000-00-00' && !empty($orderData[2])) ? new DateTime($orderData[2]) : null;
$siteLocation = $orderData[3];
$clientName = $orderData[4];
$clientContact = $orderData[5]; 
$driverName = $orderData[6];
$driverContact = $orderData[7]; 
$returnedBy = $orderData[8];
$returnedByContact = $orderData[9];
$approvedBy = $orderData[10];

// Ensure numeric values with proper defaults
$subTotal = (float)($orderData[11] ?? 0);
$vat = (float)($orderData[12] ?? 0);
$totalAmount = (float)($orderData[13] ?? 0); 
$discount = (float)($orderData[14] ?? 0);
$grandTotal = (float)($orderData[15] ?? 0);
$paid = (float)($orderData[16] ?? 0);
$due = (float)($orderData[17] ?? 0);
$paymentType = (int)($orderData[18] ?? 0);
$paymentStatus = (int)($orderData[19] ?? 0);
$paymentPlace = (int)($orderData[20] ?? 0);
$gstn = $orderData[21] ?? '';
$orderStatus = (int)($orderData[22] ?? 0);

// Calculate scheduled days
$scheduledDays = 1;
try {
    $scheduledDays = $orderDate->diff($expectReturnDate)->days + 1;
} catch (Exception $e) {
    // If calculation fails, use default
    $scheduledDays = 1;
}

// Calculate actual days
$actualDays = $scheduledDays;
if ($returnedDate) {
    try {
        $actualDays = $orderDate->diff($returnedDate)->days + 1;
    } catch (Exception $e) {
        // If calculation fails, use scheduled days
        $actualDays = $scheduledDays;
    }
}

// Format dates for display
$formattedOrderDate = $orderDate->format("d/m/Y");
$formattedExpectReturnDate = ($expectReturnDate && $orderData[1] != '0000-00-00') ? $expectReturnDate->format("d/m/Y") : 'Not Set';
$formattedReturnedDate = ($returnedDate && $orderData[2] != '0000-00-00') ? $returnedDate->format("d/m/Y") : 'Not Returned';

// Format payment type
$paymentTypeText = '';
switch($paymentType) {
    case 1: $paymentTypeText = 'Cheque'; break;
    case 2: $paymentTypeText = 'Cash'; break;
    case 3: $paymentTypeText = 'Credit Card'; break;
    default: $paymentTypeText = 'N/A';
}

// Format payment status
$paymentStatusText = '';
switch($paymentStatus) {
    case 1: $paymentStatusText = 'Full Payment'; break;
    case 2: $paymentStatusText = 'Advance Payment'; break;
    case 3: $paymentStatusText = 'No Payment'; break;
    default: $paymentStatusText = 'N/A';
}

// Format order status
$orderStatusText = '';
switch($orderStatus) {
    case 0: $orderStatusText = 'Pending'; break;
    case 1: $orderStatusText = 'Completed'; break;
    case 2: $orderStatusText = 'Cancelled'; break;
    default: $orderStatusText = 'N/A';
}

// Calculate GST breakdown
$cgst = 0;
$sgst = 0;
$igst = 0;

if($paymentPlace == 2) { // Out of Gujarat
    $igst = $subTotal * 18 / 100;
} else { // In Gujarat
    $cgst = $subTotal * 9 / 100;
    $sgst = $subTotal * 9 / 100;
}

$orderItemSql = "SELECT order_item.product_id, order_item.rate, order_item.quantity, order_item.total, product.product_name FROM order_item
   INNER JOIN product ON order_item.product_id = product.product_id 
 WHERE order_item.order_id = $orderId";
$orderItemResult = $connect->query($orderItemSql);

$table = '<style>
.star img {
    visibility: visible;
}
@media print {
    body {
        margin: 0;
        padding: 20px;
    }
    table {
        page-break-inside: avoid;
    }
}
</style>
<table align="center" cellpadding="0" cellspacing="0" style="width: 100%;border:1px solid black;margin-bottom: 10px; font-family: Arial, sans-serif;">
               <tbody>
                  <tr>
                     <td colspan="5" style="text-align:center;color: red;text-decoration: underline; font-size: 25px; padding: 10px 0;">TAX INVOICE / DELIVERY CHALLAN</td>
                  </tr>
                  <tr>
                     <td rowspan="10" colspan="2" style="border-left:1px solid black; padding: 10px;" valign="top">
                        <img src="images/logo.jpeg" alt="logo" width="250px;"><br><br>
                        <strong>Site Location:</strong> '.htmlspecialchars($siteLocation).'<br>
                        <strong>Order Status:</strong> '.htmlspecialchars($orderStatusText).'<br>
                        <strong>Expected Return:</strong> '.htmlspecialchars($formattedExpectReturnDate).'<br>
                        <strong>Returned Date:</strong> '.htmlspecialchars($formattedReturnedDate).'<br>
                        <strong>Returned By:</strong> '.($returnedBy ? htmlspecialchars($returnedBy) : 'N/A').'<br>
                        <strong>Approved By:</strong> '.($approvedBy ? htmlspecialchars($approvedBy) : 'N/A').'
                     </td>
                     <td colspan="3" style="text-align: right; padding: 5px;">ORIGINAL &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="2">Scheduled Rental Days: '.$scheduledDays.'</td>
                     <td colspan="2">Actual Rental Days: '.$actualDays.'</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">DUPLICATE &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right;color: red;font-weight: 600;text-decoration: underline;font-size: 25px; padding: 5px;">IMS &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">The Warehouse Pvt. Ltd , &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">Shop No 13, Ashok Nagar East, Surat, GJ 400059 &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">Tele: 7620361772, 7378339922 &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">Email: thewarehousepvtltd@email.co.in &nbsp;</td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">
                        <strong>Payment Type:</strong> '.htmlspecialchars($paymentTypeText).' &nbsp;<br>
                        <strong>Payment Status:</strong> '.htmlspecialchars($paymentStatusText).' &nbsp;
                     </td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;">
                        <strong>Paid:</strong> Ksh '.number_format($paid, 2).' &nbsp;<br>
                        <strong>Due:</strong> Ksh '.number_format($due, 2).' &nbsp;
                     </td>
                  </tr>
                  <tr>
                     <td colspan="3" style="text-align: right; padding: 5px;"></td>
                  </tr>
                  <tr>
                     <td colspan="2" style="padding: 0px;vertical-align: top;border-right:1px solid black;">
                        <table align="left" cellpadding="0" cellspacing="0" style="border: thin solid black; width: 100%">
                           <tbody>
                              <tr>
                                 <td style="width: 74px;vertical-align: top;color: red;" rowspan="3"> &nbsp;To, </td>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: red">&nbsp;'.htmlspecialchars($clientName).'</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: black">Client</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: black">'.htmlspecialchars($clientContact).'</td>
                              </tr>
                           </tbody>
                        </table>
                        <table align="left" cellspacing="0" style="width: 100%; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-right-width: thin; border-bottom-width: thin; border-left-width: thin; border-right-color: black; border-bottom-color: black; border-left-color: black;">
                           <tbody>
                              <tr>
                                 <td style=" border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: red;color: red;">&nbsp;G.S.T.IN : '.($gstn ? htmlspecialchars($gstn) : 'N/A').'</td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                     <td colspan="2" style="padding: 0px;vertical-align: top;border-right:1px solid black;">
                        <table align="left" cellpadding="0" cellspacing="0" style="border: thin solid black; width: 100%">
                           <tbody>
                              <tr>
                                 <td style="width: 74px;vertical-align: top;color: red;" rowspan="3"> &nbsp;Driver, </td>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: red">&nbsp;'.htmlspecialchars($driverName).'</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: black">Driver</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: black">'.htmlspecialchars($driverContact).'</td>
                              </tr>
                           </tbody>
                        </table>
                        <table align="left" cellspacing="0" style="width: 100%; border-right-style: solid; border-bottom-style: solid; border-left-style: solid; border-right-width: thin; border-bottom-width: thin; border-left-width: thin; border-right-color: black; border-bottom-color: black; border-left-color: black;">
                           <tbody>
                              <tr>
                                 <td style=" border-bottom-style: solid; border-bottom-width: thin; border-bottom-color: red;color: red;">&nbsp;Return Contact: '.($returnedByContact ? htmlspecialchars($returnedByContact) : 'N/A').'</td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                     <td style="padding: 0px;vertical-align: top;" colspan="3">
                        <table align="left" cellpadding="0" cellspacing="0" style="width: 100%">
                           <tbody>
                              <tr>
                                 <td style="border-bottom-style: solid;border-bottom-width: thin;border-bottom-color: black;border-top: 1px solid black;border-right: 1px solid black;color: red;"> &nbsp;Invoice No: '.$orderId.'</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid;border-bottom-width: thin;border-bottom-color: black;border-right: 1px solid black;color: red;">&nbsp;Date: '.$formattedOrderDate.'</td>
                              </tr>
                              <tr>
                                 <td style="border-bottom-style: solid;border-bottom-width: thin;border-bottom-color: black;height: 52px;border-right: 1px solid black;color: red;">&nbsp;Payment Place: '.($paymentPlace == 1 ? 'In Gujarat' : 'Out of Gujarat').'</td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td style="width: 60px;text-align: center;background-color: black;color: white;border-right: 1px solid white;border-left: 1px solid black;border-bottom: 1px solid black;-webkit-print-color-adjust: exact;">Sr.No</td>
                     <td style="text-align: center;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-top-width: thin;border-right-width: thin;border-bottom-width: thin;border-top-color: black;border-right-color: white;border-bottom-color: black;color: white;background-color: black;-webkit-print-color-adjust: exact;">Description Of Goods</td>
                     <td style="width: 80px;text-align: center;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-top-width: thin;border-right-width: thin;border-bottom-width: thin;border-top-color: black;border-right-color: #fff;border-bottom-color: black;background-color: black;color: white;-webkit-print-color-adjust: exact;">HSN Code</td>
                     <td style="width: 80px;text-align: center;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-top-width: thin;border-right-width: thin;border-bottom-width: thin;border-top-color: black;border-right-color: #fff;border-bottom-color: black;background-color: black;color: white;-webkit-print-color-adjust: exact;">Qty.</td>
                     <td style="width: 100px;text-align: center;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-top-width: thin;border-right-width: thin;border-bottom-width: thin;border-top-color: black;border-right-color: #fff;border-bottom-color: black;background-color: black;color: white;-webkit-print-color-adjust: exact;">Rate Ksh</td>
                     <td style="width: 120px;text-align: center;border-top-style: solid;border-right-style: solid;border-bottom-style: solid;border-top-width: thin;border-right-width: thin;border-bottom-width: thin;border-top-color: black;border-right-color: black;border-bottom-color: black;color: white;background-color: black;-webkit-print-color-adjust: exact;">Amount Ksh</td>
                  </tr>';
                  
                  $x = 1;
                  $grandTotalCalculated = 0;
                  
                  while($row = $orderItemResult->fetch_array()) {       
                    // Ensure numeric values for order items
                    $itemRate = (float)($row[1] ?? 0);
                    $itemQuantity = (int)($row[2] ?? 0);
                    $itemTotal = (float)($row[3] ?? 0);
                    $itemName = $row[4] ?? '';
                    
                    $grandTotalCalculated += $itemTotal;
                        
                    $table .= '<tr>
                         <td style="border-left: 1px solid black;border-right: 1px solid black;height: 27px; text-align: center;">'.$x.'</td>
                         <td style="border-left: 1px solid black;height: 27px; padding: 5px;">'.htmlspecialchars($itemName).'</td>
                         <td style="border-left: 1px solid black;height: 27px; text-align: center;">-</td>
                         <td style="border-left: 1px solid black;height: 27px; text-align: center;">'.$itemQuantity.'</td>
                         <td style="border-left: 1px solid black;height: 27px; text-align: right; padding-right: 10px;">'.number_format($itemRate, 2).'</td>
                         <td style="border-left: 1px solid black;border-right: 1px solid black;height: 27px; text-align: right; padding-right: 10px;">'.number_format($itemTotal, 2).'</td>
                      </tr>';
                    $x++;
                  } // /while
                  
                $table.= '
                  <tr style="border-bottom: 1px solid black;">
                     <td colspan="3" style="border-left: 1px solid black;border-right: 1px solid black;height: 27px;"></td>
                     <td style="border-left: 1px solid black;height: 27px;"></td>
                     <td style="width: 149px;border-right-style: solid;border-bottom-style: solid;border-right-width: thin;border-bottom-width: thin;border-right-color: black;border-bottom-color: #000;background-color: black;color: white;padding-left: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">Sub Total</td>
                     <td style="width: 218px; border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-top-width: thin; border-right-width: thin; border-bottom-width: thin; border-top-color: black; border-right-color: black; border-bottom-color: black; text-align: right; padding-right: 10px;">'.number_format($subTotal, 2).'</td>
                  </tr>';
                  
                  // Show discount if applicable
                  if($discount > 0) {
                    $table .= '<tr>
                         <td colspan="4" style="border-left: 1px solid black;height: 27px;"></td>
                         <td style="border-right-style: solid;border-bottom-style: solid;border-right-width: thin;border-bottom-width: thin;border-right-color: black;border-bottom-color: #000;background-color: #333;color: white;padding-left: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">Discount</td>
                         <td style="border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-top-width: thin; border-right-width: thin; border-bottom-width: thin; border-top-color: black; border-right-color: black; border-bottom-color: black; text-align: right; padding-right: 10px;">'.number_format($discount, 2).'</td>
                      </tr>';
                  }
                  
                  // Show GST based on payment place
                  if($paymentPlace == 2) { // Out of Gujarat - IGST
                    $table .= '<tr>
                         <td colspan="4" style="border-left: 1px solid black;height: 27px;"></td>
                         <td style="border-right-style: solid;border-bottom-style: solid;border-right-width: thin;border-bottom-width: thin;border-right-color: black;border-bottom-color: #000;background-color: #333;color: white;padding-left: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">IGST 18%</td>
                         <td style="border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-top-width: thin; border-right-width: thin; border-bottom-width: thin; border-top-color: black; border-right-color: black; border-bottom-color: black; text-align: right; padding-right: 10px;">'.number_format($igst, 2).'</td>
                      </tr>';
                  } else { // In Gujarat - CGST & SGST
                    $table .= '<tr>
                         <td colspan="4" style="border-left: 1px solid black;height: 27px;"></td>
                         <td style="border-right-style: solid;border-bottom-style: solid;border-right-width: thin;border-bottom-width: thin;border-right-color: black;border-bottom-color: #000;background-color: #333;color: white;padding-left: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">CGST 9%</td>
                         <td style="border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-top-width: thin; border-right-width: thin; border-bottom-width: thin; border-top-color: black; border-right-color: black; border-bottom-color: black; text-align: right; padding-right: 10px;">'.number_format($cgst, 2).'</td>
                      </tr>
                      <tr>
                         <td colspan="4" style="border-left: 1px solid black;height: 27px;"></td>
                         <td style="border-right-style: solid;border-bottom-style: solid;border-right-width: thin;border-bottom-width: thin;border-right-color: black;border-bottom-color: #000;background-color: #333;color: white;padding-left: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">SGST 9%</td>
                         <td style="border-top-style: solid; border-right-style: solid; border-bottom-style: solid; border-top-width: thin; border-right-width: thin; border-bottom-width: thin; border-top-color: black; border-right-color: black; border-bottom-color: black; text-align: right; padding-right: 10px;">'.number_format($sgst, 2).'</td>
                      </tr>';
                  }
                  
                  // Grand Total
                  $table .= '<tr>
                     <td colspan="4" style="border-left: 1px solid black;border-bottom: 1px solid black;color: red;padding: 5px;">
                        <strong>Amount in Words:</strong><br>
                        '.ucwords(convertNumberToWords($grandTotal)).' Rupees Only
                     </td>
                     <td style="border-bottom: 1px solid #fff;background-color: black;color: white;padding: 5px;-webkit-print-color-adjust: exact; text-align: right; padding-right: 10px;">Grand Total</td>
                     <td style="border-bottom: 1px solid black;border-right: 1px solid; text-align: right; padding-right: 10px; font-weight: bold;">Ksh '.number_format($grandTotal, 2).'</td>
                  </tr>
                  <tr>
                     <td colspan="3" rowspan="2" style="border-left: 1px solid black;border-bottom: 1px solid black;padding: 10px;border-right: 1px solid black; vertical-align: top;">
                        <strong>Terms & Conditions:</strong><br>
                        1. Goods once sold will not be taken back.<br>
                        2. Interest @24% p.a. will be charged if payment not made in time.<br>
                        3. All disputes subject to Surat Jurisdiction only.<br>
                        4. E. & O.E.
                     </td>
                     <td colspan="2" style="vertical-align: bottom;padding: 5px;color: red;border-right: 1px solid black;text-align: center; border-bottom: 1px solid black;"> 
                        <strong>For The Warehouse Pvt. Ltd</strong><br><br><br><br>
                        Authorized Signatory
                     </td>
                     <td style="padding: 5px;color: red;text-align: center; border-bottom: 1px solid black;">
                        <strong>Receiver\'s Signature</strong><br><br><br><br>
                        ________________
                     </td>
                  </tr>
                  <tr>
                     <td colspan="3" style="padding: 10px; text-align: center; border-left: 1px solid black;">
                        <strong>Thank You For Your Business!</strong><br>
                        Invoice Generated on: '.date("d/m/Y H:i:s").'
                     </td>
                  </tr>
               </tbody>
            </table>';

// Helper function to convert number to words
function convertNumberToWords($number) {
    // Ensure $number is numeric
    $num = (float)$number;
    
    if ($num == 0) {
        return "Zero";
    }
    
    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", 
        "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety");
    $hundreds = array("Hundred", "Thousand", "Lakh", "Crore");
    
    $words = "";
    
    // Handle crores
    if ($num >= 10000000) {
        $crores = floor($num / 10000000);
        $words .= convertNumberToWords($crores) . " Crore ";
        $num = fmod($num, 10000000);
    }
    
    // Handle lakhs
    if ($num >= 100000) {
        $lakhs = floor($num / 100000);
        $words .= convertNumberToWords($lakhs) . " Lakh ";
        $num = fmod($num, 100000);
    }
    
    // Handle thousands
    if ($num >= 1000) {
        $thousands = floor($num / 1000);
        $words .= convertNumberToWords($thousands) . " Thousand ";
        $num = fmod($num, 1000);
    }
    
    // Handle hundreds
    if ($num >= 100) {
        $hundred = floor($num / 100);
        $words .= $ones[$hundred] . " Hundred ";
        $num = fmod($num, 100);
    }
    
    // Handle tens and ones
    if ($num > 0) {
        if ($num < 20) {
            $words .= $ones[$num];
        } else {
            $ten = floor($num / 10);
            $one = fmod($num, 10);
            $words .= $tens[$ten];
            if ($one > 0) {
                $words .= " " . $ones[$one];
            }
        }
    }
    
    return $words;
}

$connect->close();

echo $table; 
?>