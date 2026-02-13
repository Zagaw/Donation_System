<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Donation Certificate</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 30px;
        }
        .certificate-border {
            border: 20px solid #2563eb;
            padding: 40px;
            min-height: 600px;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }
        .title {
            font-size: 48px;
            color: #1e40af;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 24px;
            color: #4b5563;
        }
        .content {
            text-align: center;
            padding: 30px 20px;
        }
        .certifies {
            font-size: 20px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .recipient {
            font-size: 42px;
            color: #1e40af;
            font-weight: bold;
            margin: 30px 0;
            padding: 15px 30px;
            border-bottom: 2px dashed #2563eb;
            border-top: 2px dashed #2563eb;
            display: inline-block;
        }
        .donation-details {
            margin: 40px 0;
            font-size: 20px;
            line-height: 2;
            color: #374151;
        }
        .item-name {
            font-size: 36px;
            color: #059669;
            font-weight: bold;
            margin: 20px 0;
        }
        .quantity {
            font-size: 28px;
            color: #2563eb;
            font-weight: bold;
        }
        .category {
            display: inline-block;
            background: #e2e8f0;
            padding: 8px 20px;
            border-radius: 25px;
            color: #1e40af;
            font-size: 18px;
            margin-top: 15px;
        }
        .date {
            font-size: 22px;
            color: #4b5563;
            margin-top: 30px;
        }
        .footer {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            padding: 0 60px;
        }
        .signature {
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 2px solid #1e40af;
            margin-top: 50px;
            padding-top: 10px;
            color: #1e40af;
            font-weight: bold;
        }
        .certificate-number {
            position: absolute;
            bottom: 30px;
            right: 50px;
            font-size: 14px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .donor-name {
            font-size: 18px;
            color: #4b5563;
            margin-top: 10px;
        }
        .seal {
            position: absolute;
            top: 50px;
            right: 50px;
            opacity: 0.1;
            font-size: 120px;
            color: #2563eb;
            transform: rotate(-15deg);
        }
    </style>
</head>
<body>
    <div class="certificate-border">
        <div class="seal">❤️</div>
        
        <div class="header">
            <div class="title">CERTIFICATE OF APPRECIATION</div>
            <div class="subtitle">Community Donation System</div>
        </div>
        
        <div class="content">
            <div class="certifies">This is to certify that</div>
            
            <div class="recipient">{{ $certificate->recipient_name }}</div>
            
            <div class="donation-details">
                has generously donated
                <div class="item-name">{{ $certificate->item_name }}</div>
                <div class="quantity">Quantity: {{ $certificate->quantity }}</div>
                <span class="category">{{ $certificate->category }}</span>
            </div>
            
            @if($certificate->donor_name)
                <div class="donor-name">Donor: {{ $certificate->donor_name }}</div>
            @endif
            
            <div class="date">
                Given this {{ $issue_date }}
            </div>
        </div>
        
        <div class="footer">
            <div class="signature">
                <div class="signature-line">Executive Director</div>
            </div>
            <div class="signature">
                <div class="signature-line">Program Coordinator</div>
            </div>
        </div>
        
        <div class="certificate-number">
            Certificate No: {{ $certificate->certificate_number }}
        </div>
    </div>
</body>
</html>