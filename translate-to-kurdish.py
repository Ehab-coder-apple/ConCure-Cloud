#!/usr/bin/env python3
"""
Quick Kurdish Translation Helper
Replaces Arabic translations with Kurdish (Sorani) translations
"""

import json
import sys

# Common medical/dashboard terms - Arabic to Kurdish Sorani
KURDISH_TRANSLATIONS = {
    # Dashboard & Navigation
    "لوحة التحكم": "داشبۆرد",
    "المرضى": "نەخۆشەکان",
    "المواعيد": "چاوپێکەوتنەکان",
    "الوصفات الطبية": "نووسخە پزیشکیەکان",
    "المختبر": "تاقیگە",
    "الأشعة": "تیشکدەر",
    "التغذية": "خۆراک",
    "الدواء": "دەرمان",
    "الأدوية": "دەرمانەکان",
    "المالية": "دارایی",
    "الإعدادات": "ڕێکخستنەکان",
    "تسجيل الخروج": "چوونەدەرەوە",
    "الرسائل": "پەیامەکان",
    
    # Common Actions
    "إضافة": "زیادکردن",
    "تعديل": "دەستکاریکردن",
    "حذف": "سڕینەوە",
    "حفظ": "پاشەکەوتکردن",
    "إلغاء": "هەڵوەشاندنەوە",
    "بحث": "گەڕان",
    "تصفية": "پاڵاوتن",
    "تصدير": "هەناردەکردن",
    "طباعة": "چاپکردن",
    "عرض": "پیشاندان",
    "إغلاق": "داخستن",
    
    # Patient related
    "إضافة مريض": "زیادکردنی نەخۆش",
    "مريض جديد": "نەخۆشی نوێ",
    "ملف المريض": "فایلی نەخۆش",
    "سجل المريض": "تۆمارەکانی نەخۆش",
    "معلومات المريض": "زانیاریەکانی نەخۆش",
    "الاسم الأول": "ناوی یەکەم",
    "اسم العائلة": "ناوی خێزان",
    "تاريخ الميلاد": "بەرواری لەدایکبوون",
    "العمر": "تەمەن",
    "الجنس": "ڕەگەز",
    "ذكر": "نێر",
    "أنثى": "مێ",
    "رقم الهاتف": "ژمارەی تەلەفۆن",
    "البريد الإلكتروني": "ئیمەیڵ",
    "العنوان": "ناونیشان",
    
    # Medical Terms
    "التشخيص": "دەستنیشانکردن",
    "الأعراض": "نیشانەکان",
    "العلاج": "چارەسەر",
    "الفحص": "پشکنین",
    "نتيجة الفحص": "ئەنجامی پشکنین",
    "الحساسية": "هەستیاری",
    "ضغط الدم": "پەستانی خوێن",
    "درجة الحرارة": "پلەی گەرمی",
    "الوزن": "کێش",
    "الطول": "باڵا",
    "النبض": "لێدانی دڵ",
    
    # Appointments
    "موعد جديد": "چاوپێکەوتنی نوێ",
    "المواعيد اليوم": "چاوپێکەوتنەکانی ئەمڕۆ",
    "التاريخ": "بەروار",
    "الوقت": "کات",
    "الطبيب": "پزیشک",
    "نوع الموعد": "جۆری چاوپێکەوتن",
    "حالة الموعد": "بارودۆخی چاوپێکەوتن",
    "مجدول": "خشتەکراو",
    "مكتمل": "تەواوبوو",
    "ملغى": "هەڵوەشاوە",
    
    # Common words
    "نعم": "بەڵێ",
    "لا": "نەخێر",
    "اليوم": "ئەمڕۆ",
    "أمس": "دوێنێ",
    "غداً": "سبەینێ",
    "هذا الأسبوع": "ئەم هەفتەیە",
    "هذا الشهر": "ئەم مانگە",
    "هذه السنة": "ئەمساڵ",
    "الكل": "هەموو",
    "نشط": "چالاک",
    "غير نشط": "ناچالاک",
    "محذوف": "سڕاوەتەوە",
    
    # Status & Messages
    "تم بنجاح": "سەرکەوتوو بوو",
    "فشل": "شکستی هێنا",
    "خطأ": "هەڵە",
    "تحذير": "ئاگاداری",
    "معلومات": "زانیاری",
    "تأكيد": "پشتڕاستکردنەوە",
    "هل أنت متأكد؟": "دڵنیایت؟",
    
    # Numbers & Time
    "الأول": "یەکەم",
    "الثاني": "دووەم",
    "الثالث": "سێیەم",
    "صباحاً": "بەیانی",
    "مساءً": "ئێوارە",
    "دقيقة": "خولەک",
    "ساعة": "کاتژمێر",
    "يوم": "ڕۆژ",
    "أسبوع": "هەفتە",
    "شهر": "مانگ",
    "سنة": "ساڵ",
}

def translate_json_file(input_file, output_file):
    """Translate Arabic JSON to Kurdish"""
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        translated = {}
        translated_count = 0
        
        for key, arabic_value in data.items():
            if arabic_value in KURDISH_TRANSLATIONS:
                translated[key] = KURDISH_TRANSLATIONS[arabic_value]
                translated_count += 1
            else:
                # Keep original if no translation found
                translated[key] = arabic_value
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(translated, f, ensure_ascii=False, indent=4)
        
        print(f"✅ Translated {translated_count} out of {len(data)} entries")
        print(f"📝 Output saved to: {output_file}")
        
    except Exception as e:
        print(f"❌ Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python3 translate-to-kurdish.py <input_ar.json> <output_ku.json>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    translate_json_file(input_file, output_file)
