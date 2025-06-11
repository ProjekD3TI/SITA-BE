<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[TANGGAPAN] Pelaporan Kejadian {{ $nomor_laporan }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #0066cc;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .detail-box {
            background-color: #f9f9f9;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
        }
        .response-box {
            background-color: #f0f7ff;
            border: 1px solid #d0e3ff;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #eeeeee;
        }
        .btn {
            display: inline-block;
            background-color: #0066cc;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f8f9fa;
            color: #495057;
        }
        .status-unverified {
            background-color: #ffc107;
            color: #212529;
        }
        .status-verified {
            background-color: #28a745;
            color: #ffffff;
        }
        .status-rejected {
            background-color: #dc3545;
            color: #ffffff;
        }
        .status-finished {
            background-color: #17a2b8;
            color: #ffffff;
        }
        /* Chat-style messaging */
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 15px;
            clear: both;
        }
        .message-user {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .message-bubble {
            padding: 10px 15px;
            border-radius: 18px;
            display: inline-block;
            max-width: 80%;
            position: relative;
            word-wrap: break-word;
        }
        .message-time {
            font-size: 11px;
            color: #777;
            margin-top: 3px;
            display: block;
        }
        .message-kalab .message-bubble {
            background-color: #0066cc;
            color: white;
            border-bottom-right-radius: 4px;
            float: right;
        }
        .message-kalab {
            text-align: right;
        }
        .message-other .message-bubble {
            background-color: #e5e5ea;
            color: #333;
            border-bottom-left-radius: 4px;
            float: left;
        }
        .message-other {
            text-align: left;
        }
        .chat-date-separator {
            text-align: center;
            margin: 15px 0;
            clear: both;
        }
        .chat-date {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 12px;
            color: #666;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[TANGGAPAN] Pelaporan Kejadian {{ $nomor_laporan }}</h1>
        </div>
        
        <div class="content">
            <p>Kepada Yth. {{ $nama_pelapor }},</p>
            
            <p>Ini adalah notifikasi otomatis untuk menginformasikan bahwa kepala laboratorium telah memberikan tanggapan atas laporan yang Anda ajukan.</p>
            
            <div class="detail-box">
                <div class="detail-item">
                    <span class="detail-label">Nomor Laporan:</span> {{ $nomor_laporan }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Kejadian:</span> {{ \Carbon\Carbon::parse($tanggal_kejadian)->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY, HH:mm') }} WIB
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span> 
                    <span class="status-badge status-{{ strtolower($status) }}">{{ $status }}</span>
                </div>
            </div>
            
            <h3>Ringkasan Tanggapan:</h3>
            
            <div class="response-box">
                <div class="chat-container">
                @php
                    $currentDate = '';
                    $responses = is_string($tanggapan) ? json_decode($tanggapan, true) : $tanggapan;
                @endphp
                
                @if(is_array($responses))
                    @foreach($responses as $response)
                        @php
                            // Convert to Indonesia timezone (UTC+7)
                            $timestamp = \Carbon\Carbon::parse($response['timestamp'])->setTimezone('Asia/Jakarta');
                            $messageDate = $timestamp->format('Y-m-d');
                            $formattedDate = $timestamp->isoFormat('DD MMMM YYYY');
                            $formattedTime = $timestamp->format('H:i');
                            $isKalab = isset($response['role']) && $response['role'] === 'kalab';
                            $messageClass = $isKalab ? 'message-kalab' : 'message-other';
                        @endphp
                        
                        @if($currentDate != $messageDate)
                            <div class="chat-date-separator">
                                <span class="chat-date">{{ $formattedDate }}</span>
                            </div>
                            @php $currentDate = $messageDate; @endphp
                        @endif
                        
                        <div class="message {{ $messageClass }}">
                            <div class="message-user">
                                {{ $response['user'] }}{{ isset($response['role']) ? ' (' . ucfirst($response['role']) . ')' : '' }}
                            </div>
                            <div class="message-bubble">
                                {{ $response['text'] }}
                            </div>
                            <span class="message-time">{{ $formattedTime }} WIB</span>
                        </div>
                    @endforeach
                @else
                    <p>{{ $tanggapan }}</p>
                @endif
                </div>
            </div>
            
            <p>Jika Anda memiliki pertanyaan lebih lanjut mengenai tanggapan yang diberikan, silakan hubungi kepala laboratorium melalui sistem atau secara langsung.</p>
            
            <p>Terima kasih atas pelaporan Anda.</p>
            
            <p>Salam,</p>
            <p><strong>Sistem Informasi Pelaporan D3TI UNS</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} D3TI UNS - Sistem Informasi Pelaporan</p>
        </div>
    </div>
</body>
</html>