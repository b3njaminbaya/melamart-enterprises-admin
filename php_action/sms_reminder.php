<?php
require_once 'php_action/db_connect.php';
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;

class SMSReminder {
    private $connect;
    private $sms;
    
    public function __construct($connect) {
        $this->connect = $connect;
        
        // Initialize Africa's Talking SDK
        $username = "MyAppsUsername"; // Replace with your username
        $apiKey = "MyAppAPIKey"; // Replace with your API key
        $AT = new AfricasTalking($username, $apiKey);
        $this->sms = $AT->sms();
    }
    
    public function checkOverdueOrders() {
        $today = date('Y-m-d');
        
        // Find orders where:
        // 1. Expected return date is today or passed
        // 2. Order is not completed (order_status = 0 = Pending)
        // 3. No returned date set (or returned_date is null/0000-00-00)
        // 4. Items haven't been returned
        $sql = "SELECT o.*, a.admin_name, a.admin_contact 
                FROM orders o
                LEFT JOIN admin a ON 1=1 
                WHERE o.expect_return_date <= '$today' 
                AND o.order_status = 0 
                AND (o.returned_date IS NULL OR o.returned_date = '0000-00-00') 
                LIMIT 1"; // Assuming there's at least one admin
        
        $result = $this->connect->query($sql);
        $overdueOrders = [];
        
        while($row = $result->fetch_assoc()) {
            $overdueOrders[] = $row;
        }
        
        return $overdueOrders;
    }
    
    public function sendAdminReminder($order, $adminContact) {
        try {
            $clientName = $order['client_name'];
            $expectedDate = date('d-m-Y', strtotime($order['expect_return_date']));
            $orderId = $order['order_id'];
            
            $message = "REMINDER: Order #{$orderId} is overdue!\n";
            $message .= "Client: {$clientName}\n";
            $message .= "Expected Return: {$expectedDate}\n";
            $message .= "Contact Client: {$order['client_contact']}\n";
            $message .= "Please follow up on item returns.";
            
            $result = $this->sms->send([
                'to'      => $adminContact,
                'message' => $message,
                'from'    => 'RENTALSYS' // Your sender ID
            ]);
            
            // Log the SMS sent
            $this->logSmsSent($orderId, $adminContact, $message);
            
            return ['success' => true, 'result' => $result];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function logSmsSent($orderId, $recipient, $message) {
        $sql = "INSERT INTO sms_logs (order_id, recipient, message, sent_date) 
                VALUES ('$orderId', '$recipient', '" . $this->connect->real_escape_string($message) . "', NOW())";
        $this->connect->query($sql);
    }
    
    public function processReminders() {
        $overdueOrders = $this->checkOverdueOrders();
        
        if(empty($overdueOrders)) {
            return ['reminders_sent' => 0, 'message' => 'No overdue orders found'];
        }
        
        $sentCount = 0;
        $responses = [];
        
        foreach($overdueOrders as $order) {
            // Get admin contact - you might need to adjust this based on your admin table structure
            $adminSql = "SELECT admin_contact FROM admin WHERE admin_id = 1 LIMIT 1";
            $adminResult = $this->connect->query($adminSql);
            $admin = $adminResult->fetch_assoc();
            
            if($admin && !empty($admin['admin_contact'])) {
                $response = $this->sendAdminReminder($order, $admin['admin_contact']);
                if($response['success']) {
                    $sentCount++;
                    $responses[] = "Reminder sent for Order #{$order['order_id']}";
                    
                    // Update order to mark reminder sent (optional)
                    $this->updateReminderSent($order['order_id']);
                }
            }
        }
        
        return [
            'reminders_sent' => $sentCount,
            'responses' => $responses
        ];
    }
    
    private function updateReminderSent($orderId) {
        $sql = "UPDATE orders SET reminder_sent = 1, last_reminder_date = NOW() WHERE order_id = '$orderId'";
        $this->connect->query($sql);
    }
}

// To run the reminder manually (for testing)
// $smsReminder = new SMSReminder($connect);
// $result = $smsReminder->processReminders();
// print_r($result);
?>