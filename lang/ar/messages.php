<?php

return [
    'saved' => 'تم الحفظ بنجاح.',
    'deleted' => 'تم الحذف بنجاح.',
    'updated' => 'تم التحديث بنجاح.',
    'sales' => [
        'invoice_created' => 'تم إنشاء فاتورة المبيعات.',
        'invoice_posted' => 'تم ترحيل الفاتورة وتحديث المخزون.',
        'invoice_voided' => 'تم إلغاء الفاتورة.',
        'invoice_updated' => 'تم تحديث الفاتورة المرحّلة.',
    ],
    'purchase' => [
        'invoice_created' => 'تم إنشاء فاتورة المشتريات.',
        'invoice_posted' => 'تم ترحيل الفاتورة واستلام المخزون.',
        'invoice_voided' => 'تم إلغاء فاتورة المشتريات.',
        'invoice_updated' => 'تم تحديث فاتورة المشتريات المرحّلة.',
    ],
    'proforma' => [
        'created' => 'تم إنشاء الفاتورة الأولية.',
        'converted' => 'تم تحويل الفاتورة الأولية إلى فاتورة :no.',
    ],
    'pos' => [
        'session_opened' => 'تم فتح جلسة نقطة البيع.',
        'session_closed' => 'تم إغلاق جلسة نقطة البيع.',
        'sale_completed' => 'تم إتمام البيع: :no.',
    ],
    'pick' => [
        'created' => 'تم إنشاء أذن الصرف :no.',
        'confirmed' => 'تم تأكيد الصرف.',
    ],
    'cashbook' => [
        'entry_recorded' => 'تم تسجيل قيد دفتر النقدية.',
    ],
    'fixed_asset' => [
        'registered' => 'تم تسجيل الأصل الثابت.',
        'depreciation_posted' => 'تم ترحيل الإهلاك لـ :count أصل.',
    ],
    'currency' => [
        'created' => 'تم إنشاء العملة.',
        'updated' => 'تم تحديث العملة.',
        'deleted' => 'تم حذف العملة.',
        'rate_updated' => 'تم تحديث سعر الصرف.',
        'cannot_delete_base' => 'لا يمكن حذف العملة الأساسية.',
    ],
    'user' => [
        'updated' => 'تم تحديث المستخدم.',
        'permissions_updated' => 'تم تحديث صلاحيات المستخدم.',
        'cannot_remove_own_admin' => 'لا يمكنك إزالة دور المدير عن نفسك.',
    ],
    'showroom' => [
        'vehicle_registered' => 'تم تسجيل مركبة صالة العرض.',
        'transfer_created' => 'تم بدء تحويل المركبة.',
        'transfer_received' => 'تم استلام تحويل المركبة.',
        'vehicle_sold' => 'تم تسجيل المركبة كمباعة.',
        'no_vehicles' => 'لا توجد مركبات في صالة العرض.',
    ],
    'delivery' => [
        'created' => 'تم إنشاء إشعار التسليم.',
    ],
    'cheque' => [
        'recorded' => 'تم تسجيل الشيك.',
        'status_updated' => 'تم تحديث حالة الشيك.',
    ],
    'journal' => [
        'posted' => 'تم ترحيل قيد اليومية اليدوي.',
    ],
    'kit' => [
        'component_added' => 'تمت إضافة مكون الطقم.',
        'component_removed' => 'تمت إزالة مكون الطقم.',
        'alternative_added' => 'تمت إضافة قطعة بديلة.',
        'alternative_removed' => 'تمت إزالة البديل.',
    ],
    'stock_count' => [
        'saved' => 'تم حفظ جلسة الجرد.',
        'posted' => 'تم ترحيل الجرد — تمت تسوية الفروقات.',
    ],
    'fiscal' => [
        'closed' => 'تم إغلاق الفترة.',
        'reopened' => 'تم إعادة فتح الفترة.',
    ],
    'payment' => [
        'recorded' => 'تم تسجيل الدفعة.',
    ],
    'return' => [
        'purchase_created' => 'تم إنشاء مرتجع المشتريات.',
        'purchase_posted' => 'تم ترحيل المرتجع وخصم المخزون.',
        'sale_created' => 'تم إنشاء مرتجع المبيعات.',
        'sale_posted' => 'تم ترحيل المرتجع واستعادة المخزون.',
    ],
    'attendance' => [
        'saved' => 'تم حفظ الحضور.',
    ],
    'payroll' => [
        'generated' => 'تم إنشاء مسير الرواتب.',
        'posted' => 'تم ترحيل الرواتب.',
    ],
    'job_card' => [
        'created' => 'تم إنشاء بطاقة العمل.',
        'status_updated' => 'تم تحديث حالة بطاقة العمل.',
        'converted' => 'تم تحويل بطاقة العمل إلى فاتورة :no.',
    ],
    'settings' => [
        'saved' => 'تم حفظ الإعدادات.',
        'discount_added' => 'تمت إضافة قاعدة الخصم.',
        'discount_removed' => 'تمت إزالة قاعدة الخصم.',
    ],
    'quotation' => [
        'created' => 'تم إنشاء عرض السعر.',
        'converted' => 'تم تحويل عرض السعر إلى فاتورة :no.',
    ],
    'master' => [
        'created' => 'تم إنشاء السجل بنجاح.',
        'updated' => 'تم تحديث السجل بنجاح.',
        'deleted' => 'تم حذف السجل بنجاح.',
    ],
    'stock' => [
        'adjusted' => 'تمت تسوية المخزون بنجاح.',
        'transfer_created' => 'تم إنشاء تحويل المخزون.',
        'transfer_completed' => 'اكتمل التحويل. تم نقل المخزون بين الفروع.',
    ],
    'part' => [
        'created' => 'تم إنشاء القطعة بنجاح.',
        'updated' => 'تم تحديث القطعة بنجاح.',
        'deleted' => 'تم حذف القطعة بنجاح.',
    ],
    'purchase_order' => [
        'created' => 'تم إنشاء أمر الشراء.',
        'converted' => 'تم إنشاء فاتورة المشتريات :no من أمر الشراء.',
    ],
    'vehicle' => [
        'order_created' => 'تم إنشاء طلب المركبة.',
        'order_updated' => 'تم تحديث الطلب.',
        'order_deleted' => 'تم حذف الطلب.',
        'expense_recorded' => 'تم تسجيل المصروف.',
        'expense_removed' => 'تمت إزالة المصروف.',
    ],
];
