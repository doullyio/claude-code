/**
 * Med Africa - Google Sheets Setup Script
 * يقوم بإنشاء وتهيئة جدول الشكاوي مع الإحصائيات والتنسيق
 */

// ============================
// 1. إنشاء ورقة الشكاوي
// ============================
function setupComplaintsSheet() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  // حذف الورقة إذا كانت موجودة
  let sheet = ss.getSheetByName('الشكاوي');
  if (sheet) {
    ss.deleteSheet(sheet);
  }

  // إنشاء ورقة جديدة
  sheet = ss.insertSheet('الشكاوي');

  // تعريف الأعمدة (15 عمود)
  const headers = [
    'رقم التذكرة',  // A
    'التاريخ',       // B
    'الوقت',         // C
    'الاسم',         // D
    'اللقب',         // E
    'المدينة',       // F
    'رقم الهاتف',    // G
    'رقم التتبع',    // H
    'النوع',         // I
    'الفئة',         // J
    'الأولوية',      // K
    'الحالة',        // L
    'الوصف',         // M
    'المسؤول',       // N
    'ملاحظات'        // O
  ];

  // كتابة العناوين
  const headerRange = sheet.getRange(1, 1, 1, headers.length);
  headerRange.setValues([headers]);

  // تنسيق صف العناوين
  headerRange.setBackground('#1D9E75');
  headerRange.setFontColor('#FFFFFF');
  headerRange.setFontWeight('bold');
  headerRange.setFontSize(11);
  headerRange.setFontFamily('Cairo');
  headerRange.setHorizontalAlignment('center');
  headerRange.setVerticalAlignment('middle');
  headerRange.setWrap(true);

  // تجميد الصف الأول
  sheet.setFrozenRows(1);

  // ضبط عرض الأعمدة
  const columnWidths = [120, 100, 80, 120, 120, 100, 130, 150, 80, 100, 80, 100, 250, 120, 200];
  columnWidths.forEach((width, i) => {
    sheet.setColumnWidth(i + 1, width);
  });

  // ضبط ارتفاع صف العنوان
  sheet.setRowHeight(1, 40);

  // تطبيق RTL
  sheet.setRightToLeft(true);

  // ============================
  // 4. التحقق من صحة البيانات - الأولوية
  // ============================
  const priorityRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['عاجل', 'مهم', 'عادي'], true)
    .setAllowInvalid(false)
    .setHelpText('اختر الأولوية: عاجل، مهم، أو عادي')
    .build();

  // تطبيق على العمود K (الأولوية) - من الصف 2 إلى 1000
  sheet.getRange('K2:K1000').setDataValidation(priorityRule);

  // ============================
  // 5. التحقق من صحة البيانات - الحالة
  // ============================
  const statusRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['جديد', 'قيد_المعالجة', 'في_الانتظار', 'محلول', 'مغلق'], true)
    .setAllowInvalid(false)
    .setHelpText('اختر الحالة: جديد، قيد_المعالجة، في_الانتظار، محلول، أو مغلق')
    .build();

  // تطبيق على العمود L (الحالة) - من الصف 2 إلى 1000
  sheet.getRange('L2:L1000').setDataValidation(statusRule);

  // ============================
  // 3. التنسيق الشرطي
  // ============================
  applyConditionalFormatting(sheet);

  // ضبط خط كل الورقة
  sheet.getRange('A:O').setFontFamily('Cairo');

  Logger.log('✅ تم إنشاء ورقة الشكاوي بنجاح');
}

// ============================
// التنسيق الشرطي
// ============================
function applyConditionalFormatting(sheet) {
  // مسح التنسيقات الشرطية الموجودة
  sheet.clearConditionalFormatRules();

  const rules = [];
  const dataRange = sheet.getRange('A2:O1000');

  // الأولوية "عاجل" → خلفية حمراء
  rules.push(
    SpreadsheetApp.newConditionalFormatRule()
      .whenFormulaSatisfied('=$K2="عاجل"')
      .setBackground('#FFCDD2')
      .setRanges([dataRange])
      .build()
  );

  // الأولوية "مهم" → خلفية صفراء
  rules.push(
    SpreadsheetApp.newConditionalFormatRule()
      .whenFormulaSatisfied('=$K2="مهم"')
      .setBackground('#FFF9C4')
      .setRanges([dataRange])
      .build()
  );

  // الحالة "محلول" → نص أخضر
  rules.push(
    SpreadsheetApp.newConditionalFormatRule()
      .whenFormulaSatisfied('=$L2="محلول"')
      .setFontColor('#1B5E20')
      .setRanges([dataRange])
      .build()
  );

  sheet.setConditionalFormatRules(rules);
  Logger.log('✅ تم تطبيق التنسيق الشرطي');
}

// ============================
// 2. إنشاء ورقة الإحصائيات
// ============================
function setupStatsSheet() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  // حذف الورقة إذا كانت موجودة
  let sheet = ss.getSheetByName('إحصائيات');
  if (sheet) {
    ss.deleteSheet(sheet);
  }

  sheet = ss.insertSheet('إحصائيات');
  sheet.setRightToLeft(true);

  // ============================
  // إحصائيات عامة
  // ============================
  sheet.getRange('A1').setValue('📊 إحصائيات الشكاوي - Med Africa');
  sheet.getRange('A1').setFontSize(16).setFontWeight('bold').setFontFamily('Cairo').setFontColor('#1D9E75');
  sheet.getRange('A1:D1').merge();

  // اليوم / الأسبوع / الشهر
  const statsHeaders = ['المؤشر', 'اليوم', 'هذا الأسبوع', 'هذا الشهر'];
  sheet.getRange('A3:D3').setValues([statsHeaders]);
  sheet.getRange('A3:D3').setBackground('#1D9E75').setFontColor('#FFFFFF').setFontWeight('bold').setFontFamily('Cairo');

  // إجمالي الشكاوي
  sheet.getRange('A4').setValue('إجمالي الشكاوي');
  sheet.getRange('B4').setFormula('=COUNTIFS(الشكاوي!B:B,TEXT(TODAY(),"yyyy-mm-dd"))');
  sheet.getRange('C4').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(TODAY()-WEEKDAY(TODAY(),2)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"))');
  sheet.getRange('D4').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(EOMONTH(TODAY(),-1)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"))');

  // شكاوي عاجلة
  sheet.getRange('A5').setValue('شكاوي عاجلة');
  sheet.getRange('B5').setFormula('=COUNTIFS(الشكاوي!B:B,TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!K:K,"عاجل")');
  sheet.getRange('C5').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(TODAY()-WEEKDAY(TODAY(),2)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!K:K,"عاجل")');
  sheet.getRange('D5').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(EOMONTH(TODAY(),-1)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!K:K,"عاجل")');

  // شكاوي محلولة
  sheet.getRange('A6').setValue('محلولة');
  sheet.getRange('B6').setFormula('=COUNTIFS(الشكاوي!B:B,TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!L:L,"محلول")');
  sheet.getRange('C6').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(TODAY()-WEEKDAY(TODAY(),2)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!L:L,"محلول")');
  sheet.getRange('D6').setFormula('=COUNTIFS(الشكاوي!B:B,">="&TEXT(EOMONTH(TODAY(),-1)+1,"yyyy-mm-dd"),الشكاوي!B:B,"<="&TEXT(TODAY(),"yyyy-mm-dd"),الشكاوي!L:L,"محلول")');

  // مفتوحة
  sheet.getRange('A7').setValue('مفتوحة (قيد المعالجة)');
  sheet.getRange('B7').setFormula('=COUNTIFS(الشكاوي!L:L,"<>محلول",الشكاوي!L:L,"<>مغلق",الشكاوي!L:L,"<>")');

  sheet.getRange('A4:A7').setFontFamily('Cairo').setFontWeight('bold');
  sheet.getRange('B4:D7').setFontFamily('Cairo').setHorizontalAlignment('center').setFontSize(14);

  // ============================
  // توزيع حسب الفئة
  // ============================
  sheet.getRange('A9').setValue('📈 توزيع حسب الفئة');
  sheet.getRange('A9').setFontSize(14).setFontWeight('bold').setFontFamily('Cairo').setFontColor('#1D9E75');
  sheet.getRange('A9:B9').merge();

  const categories = ['تأخير', 'ضياع', 'تلف', 'منتج_خطأ', 'تتبع', 'إرجاع', 'توصيل', 'عام'];
  categories.forEach((cat, i) => {
    const row = 10 + i;
    sheet.getRange(`A${row}`).setValue(cat).setFontFamily('Cairo');
    sheet.getRange(`B${row}`).setFormula(`=COUNTIF(الشكاوي!J:J,"${cat}")`).setFontFamily('Cairo').setHorizontalAlignment('center');
  });

  // إنشاء رسم بياني دائري للفئات
  const categoryChart = sheet.newChart()
    .setChartType(Charts.ChartType.PIE)
    .addRange(sheet.getRange('A10:B17'))
    .setPosition(9, 4, 0, 0)
    .setOption('title', 'توزيع الشكاوي حسب الفئة')
    .setOption('titleTextStyle', { fontName: 'Cairo', fontSize: 12 })
    .setOption('pieHole', 0.4)
    .setOption('colors', ['#1D9E75', '#E53935', '#FB8C00', '#8E24AA', '#1E88E5', '#43A047', '#FDD835', '#78909C'])
    .setOption('width', 400)
    .setOption('height', 300)
    .build();

  sheet.insertChart(categoryChart);

  // ============================
  // توزيع حسب المدينة
  // ============================
  sheet.getRange('A19').setValue('🏙️ توزيع حسب المدينة');
  sheet.getRange('A19').setFontSize(14).setFontWeight('bold').setFontFamily('Cairo').setFontColor('#1D9E75');
  sheet.getRange('A19:B19').merge();

  const cities = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة', 'أكادير', 'مكناس', 'وجدة', 'القنيطرة', 'أخرى'];
  cities.forEach((city, i) => {
    const row = 20 + i;
    sheet.getRange(`A${row}`).setValue(city).setFontFamily('Cairo');
    sheet.getRange(`B${row}`).setFormula(`=COUNTIF(الشكاوي!F:F,"${city}")`).setFontFamily('Cairo').setHorizontalAlignment('center');
  });

  // ============================
  // متوسط وقت الحل
  // ============================
  sheet.getRange('A31').setValue('⏱️ متوسط وقت الحل');
  sheet.getRange('A31').setFontSize(14).setFontWeight('bold').setFontFamily('Cairo').setFontColor('#1D9E75');
  sheet.getRange('A31:B31').merge();

  sheet.getRange('A32').setValue('ملاحظة: يحسب تلقائيا عند إضافة عمود تاريخ الحل').setFontFamily('Cairo').setFontColor('#999999');
  sheet.getRange('A32:D32').merge();

  // ضبط عرض الأعمدة
  sheet.setColumnWidth(1, 180);
  sheet.setColumnWidth(2, 120);
  sheet.setColumnWidth(3, 120);
  sheet.setColumnWidth(4, 120);

  Logger.log('✅ تم إنشاء ورقة الإحصائيات بنجاح');
}

// ============================
// 6. تشغيل الإعداد الكامل
// ============================
function runFullSetup() {
  Logger.log('🚀 بدء إعداد جدول Med Africa...');

  setupComplaintsSheet();
  setupStatsSheet();

  // ضبط الخط الافتراضي للملف
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  // إعادة ترتيب الأوراق
  const complaintsSheet = ss.getSheetByName('الشكاوي');
  const statsSheet = ss.getSheetByName('إحصائيات');

  if (complaintsSheet) ss.setActiveSheet(complaintsSheet);
  if (complaintsSheet) ss.moveActiveSheet(1);
  if (statsSheet) {
    ss.setActiveSheet(statsSheet);
    ss.moveActiveSheet(2);
  }

  // تفعيل ورقة الشكاوي
  if (complaintsSheet) ss.setActiveSheet(complaintsSheet);

  Logger.log('✅ تم الإعداد الكامل بنجاح!');
  SpreadsheetApp.getUi().alert('✅ تم إعداد جدول Med Africa بنجاح!\n\nتم إنشاء:\n- ورقة الشكاوي (15 عمود)\n- ورقة الإحصائيات (مع رسوم بيانية)\n- التنسيق الشرطي\n- التحقق من صحة البيانات');
}

// ============================
// قائمة مخصصة
// ============================
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('Med Africa')
    .addItem('🚀 إعداد كامل', 'runFullSetup')
    .addSeparator()
    .addItem('📋 إنشاء ورقة الشكاوي', 'setupComplaintsSheet')
    .addItem('📊 إنشاء ورقة الإحصائيات', 'setupStatsSheet')
    .addSeparator()
    .addItem('🎨 إعادة التنسيق الشرطي', 'reapplyFormatting')
    .addToUi();
}

function reapplyFormatting() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName('الشكاوي');
  if (sheet) {
    applyConditionalFormatting(sheet);
    SpreadsheetApp.getUi().alert('✅ تم إعادة التنسيق الشرطي بنجاح');
  } else {
    SpreadsheetApp.getUi().alert('❌ لم يتم العثور على ورقة الشكاوي');
  }
}
