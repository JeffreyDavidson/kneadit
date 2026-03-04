<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KneadIt Bakery')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f5f1;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #d4a574 0%, #c19653 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 40px;
        }
        .status-badge {
            display: inline-block;
            background-color: @yield('badge-color', '#d4a574');
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #faf9f7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #d4a574;
        }
        .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #8b4513;
            margin-bottom: 10px;
        }
        .order-items {
            margin: 15px 0;
        }
        .order-item {
            padding: 10px 0;
            border-bottom: 1px solid #e8e6e3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-weight: 500;
            color: #333;
        }
        .item-details {
            font-size: 14px;
            color: #666;
        }
        .item-price {
            font-weight: 600;
            color: #8b4513;
        }
        .order-total {
            background-color: #8b4513;
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-top: 15px;
            text-align: right;
        }
        .total-label {
            font-size: 16px;
            font-weight: 600;
        }
        .total-amount {
            font-size: 20px;
            font-weight: 700;
        }
        .delivery-info {
            background-color: #f0f8ff;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4169e1;
        }
        .info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .footer {
            background-color: #8b4513;
            color: white;
            text-align: center;
            padding: 30px 20px;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .contact-info {
            opacity: 0.9;
            margin-top: 15px;
        }
        .divider {
            height: 1px;
            background-color: #e8e6e3;
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px 25px;
            }
            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-price {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🥖 KneadIt Bakery</h1>
            <p>Freshly Baked with Love</p>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p><strong>Thank you for choosing KneadIt Bakery!</strong></p>
            <div class="contact-info">
                <p>📧 hello@kneaditbakery.com | 📞 (555) 123-BAKE</p>
                <p>🏠 123 Baker Street, Sweet City, SC 12345</p>
            </div>
        </div>
    </div>
</body>
</html>