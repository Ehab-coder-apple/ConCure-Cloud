<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "amiri-regular", "dejavu sans";
            font-size: 16px;
            line-height: 1.8;
            margin: 20px;
        }
        
        .kurdish {
            font-family: "amiri-regular", "dejavu sans";
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
            font-size: 18px;
            line-height: 2;
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        
        .english {
            direction: ltr;
            text-align: left;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Kurdish Font Test</h1>
    
    <div class="english">English Text: This should display normally</div>
    
    <div class="kurdish">کوردی - ماسی سەلمۆن</div>
    <div class="kurdish">برنجی قاوەیی</div>
    <div class="kurdish">پەتاتەی شیرین</div>
    <div class="kurdish">یۆگورتی یۆنانی</div>
    <div class="kurdish">سنگی مریشک</div>
    
    <div class="english">If Kurdish text above shows connected letters, the font is working!</div>
</body>
</html>
