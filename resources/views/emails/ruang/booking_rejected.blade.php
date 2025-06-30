@ -0,0 +1,158 @@
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Rejected</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 2px solid #000000;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #000000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .content {
            margin-bottom: 30px;
        }
        .booking-details {
            border: 1px solid #000000;
            padding: 20px;
            margin: 20px 0;
        }
        .booking-details h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000000;
            padding-bottom: 5px;
        }
        .detail-row {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #000000;
            padding-bottom: 5px;
        }
        .detail-label {
            font-weight: bold;
            width: 40%;
        }
        .detail-value {
            width: 60%;
            text-align: right;
        }
        .footer {
            border-top: 1px solid #000000;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
        }
        .status-rejected {
            background-color: #ffffff;
            border: 2px solid #000000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }
        .notes-section {
            border: 1px solid #000000;
            padding: 15px;
            margin: 20px 0;
            background-color: #f9f9f9;
        }
        .notes-section h4 {
            margin: 0 0 10px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Notification</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $booking->user->name }},</p>
            
            <div class="status-rejected">
                Your booking request has been rejected
            </div>
            
            <p>We regret to inform you that your room booking request has been rejected by {{ $booking->approved_by }}.</p>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value">{{ $booking->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Room:</span>
                    <span class="detail-value">{{ $booking->ruang->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->date)->format('d F Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ $booking->start_session }} - {{ $booking->end_session }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Processed By:</span>
                    <span class="detail-value">{{ $booking->approved_by }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Processed Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($booking->approved_at)->format('d F Y H:i') }}</span>
                </div>
            </div>
            
            @if($booking->notes)
            <div class="notes-section">
                <h4>Rejection Reason:</h4>
                <p>{{ $booking->notes }}</p>
            </div>
            @endif
            
            <p>You may submit a new booking request for different time slots or dates. Please contact the administration office if you have any questions regarding this decision.</p>
            
            <p>Thank you for your understanding.</p>
            
            <p>Best regards,<br>
            <strong>Booking Management System</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>Generated on {{ now()->format('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>