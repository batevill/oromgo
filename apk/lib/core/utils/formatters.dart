import 'package:intl/intl.dart';

class Formatters {
  static String formatCurrency(dynamic amount, {String currency = 'USD'}) {
    if (amount == null) return '0 $currency';
    final num value = amount is num ? amount : (num.tryParse(amount.toString()) ?? 0);
    final formatter = NumberFormat('#,###', 'uz');
    final formatted = formatter.format(value).replaceAll(',', ' ');
    final symbol = currency == 'UZS' ? "so'm" : '\$';
    return '$formatted $symbol';
  }

  static String formatDate(dynamic date) {
    if (date == null) return '';
    try {
      final DateTime dt = date is DateTime ? date : DateTime.parse(date.toString());
      return DateFormat('yyyy-MM-dd').format(dt);
    } catch (_) {
      return date.toString();
    }
  }

  static String formatTimeAgo(dynamic dateStr) {
    if (dateStr == null) return '';
    try {
      final DateTime dt = dateStr is DateTime ? dateStr : DateTime.parse(dateStr.toString());
      final Duration diff = DateTime.now().difference(dt);

      if (diff.inSeconds < 60) return 'Hozirgina';
      if (diff.inMinutes < 60) return '${diff.inMinutes} daqiqa oldin';
      if (diff.inHours < 24) return '${diff.inHours} soat oldin';
      if (diff.inDays < 7) return '${diff.inDays} kun oldin';
      return DateFormat('dd.MM.yyyy').format(dt);
    } catch (_) {
      return dateStr.toString();
    }
  }
}
