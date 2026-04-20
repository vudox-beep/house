<?php 
header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST'); 
header('Access-Control-Allow-Headers: Content-Type'); 

require_once '../config/config.php'; 
require_once '../includes/SimpleSMTP.php'; // Use the custom SpaceMail SMTP class

// Only execute the API logic if this file is called directly via a web request 
// Check if the script is the main entry point 
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) { 
    
    $data = json_decode(file_get_contents("php://input"), true); 
    if (!$data) $data = $_POST; 

    $action = $data['action'] ?? ''; 

    if (empty($action)) { 
        echo json_encode(['status' => 'error', 'message' => 'Action is required']); 
        exit; 
    } 

    $mailer = new SimpleSMTP(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS); 

    try { 
        // ========================================== 
        // ACTION: SEND FORGOT PASSWORD EMAIL 
        // ========================================== 
        if ($action === 'forgot_password') { 
            $email = $data['email'] ?? ''; 
            $reset_token = $data['reset_token'] ?? ''; // e.g. a 6-digit code or hash generated in flutter/backend 

            if (empty($email) || empty($reset_token)) { 
                echo json_encode(['status' => 'error', 'message' => 'Email and Reset Token are required']); 
                exit; 
            } 

            $subject = "Password Reset Request - " . SITE_NAME; 
            $body = " 
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'> 
                <h2 style='color: #2c3e50; text-align: center;'>Password Reset Request</h2> 
                <p>Hello,</p> 
                <p>We received a request to reset your password. Here is your password reset code:</p> 
                <div style='text-align: center; margin: 20px 0;'> 
                    <span style='font-size: 24px; font-weight: bold; background: #f4f4f4; padding: 10px 20px; border-radius: 5px; letter-spacing: 2px;'>{$reset_token}</span> 
                </div> 
                <p>Enter this code in the app to set a new password.</p> 
                <p style='color: #888; font-size: 12px;'>If you did not request this, please ignore this email.</p> 
            </div>"; 

            if ($mailer->send($email, $subject, $body, SITE_NAME)) { 
                echo json_encode(['status' => 'success', 'message' => 'Password reset email sent successfully']); 
            } else { 
                echo json_encode(['status' => 'error', 'message' => 'Failed to connect to SMTP server or send email.']); 
            } 
        } 

        // ========================================== 
        // ACTION: SEND REGISTRATION VERIFICATION EMAIL 
        // ========================================== 
        elseif ($action === 'registration_verify') { 
            $email = $data['email'] ?? ''; 
            $name = $data['name'] ?? 'User'; 
            $verify_link = $data['verify_link'] ?? ''; // e.g. ` `https://houseforrent.site/verify?token=123` ` 

            if (empty($email) || empty($verify_link)) { 
                echo json_encode(['status' => 'error', 'message' => 'Email and Verify Link are required']); 
                exit; 
            } 

            $subject = "Welcome to " . SITE_NAME . " - Verify Your Email"; 
            $body = " 
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'> 
                <h2 style='color: #2c3e50; text-align: center;'>Welcome to " . SITE_NAME . "!</h2> 
                <p>Hello {$name},</p> 
                <p>Thank you for registering with us. Please verify your email address by clicking the button below:</p> 
                <div style='text-align: center; margin: 25px 0;'> 
                    <a href='{$verify_link}' style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify My Email</a> 
                </div> 
                <p>Or copy and paste this link into your browser:</p> 
                <p style='word-break: break-all; color: #007bff;'>{$verify_link}</p> 
            </div>"; 

            if ($mailer->send($email, $subject, $body, SITE_NAME)) { 
                echo json_encode(['status' => 'success', 'message' => 'Verification email sent successfully']); 
            } else { 
                echo json_encode(['status' => 'error', 'message' => 'Failed to connect to SMTP server or send email.']); 
            } 
        } 

        // ========================================== 
        // ACTION: SEND TENANT ADDED EMAIL 
        // ========================================== 
        elseif ($action === 'tenant_added') { 
            $email = $data['email'] ?? ''; 
            $name = $data['name'] ?? 'Tenant'; 
            $temp_password = $data['temp_password'] ?? ''; 
            $payment_reference = $data['payment_reference'] ?? ''; 
            $property_title = $data['property_title'] ?? 'your new rental'; 

            if (empty($email) || empty($payment_reference)) { 
                echo json_encode(['status' => 'error', 'message' => 'Email and Payment Reference are required']); 
                exit; 
            } 

            $subject = "Welcome to your new home! - " . SITE_NAME; 
            
            // Build the email body based on if they need a temp password 
            $body = " 
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'> 
                <h2 style='color: #2c3e50; text-align: center;'>You've been added as a Tenant!</h2> 
                <p>Hello {$name},</p> 
                <p>Your dealer has successfully added you to <b>{$property_title}</b>.</p>"; 

            if (!empty($temp_password)) { 
                $body .= " 
                <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'> 
                    <p style='margin: 0 0 10px 0;'><b>Your Login Credentials:</b></p> 
                    <p style='margin: 0;'>Email: {$email}</p> 
                    <p style='margin: 0;'>Password: {$temp_password}</p> 
                    <p style='font-size: 12px; color: red; margin-top: 5px;'>Please log in and change this password immediately.</p> 
                </div>"; 
            } 

            $body .= " 
                <p><b>Important Payment Information:</b></p> 
                <p>Please use the following 16-digit Reference ID for all bank deposits and payments:</p> 
                <div style='text-align: center; margin: 20px 0;'> 
                    <span style='font-size: 22px; font-weight: bold; background: #e8f4fd; color: #0056b3; padding: 10px 20px; border-radius: 5px; letter-spacing: 2px;'>{$payment_reference}</span> 
                </div> 
                <p>Log in to the app to view your full rental details.</p> 
            </div>"; 

            if ($mailer->send($email, $subject, $body, SITE_NAME)) { 
                echo json_encode(['status' => 'success', 'message' => 'Tenant welcome email sent successfully']); 
            } else { 
                echo json_encode(['status' => 'error', 'message' => 'Failed to connect to SMTP server or send email.']); 
            } 
        }  
        else { 
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']); 
        } 

    } catch (Exception $e) { 
        echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]); 
    } 
} 
?>