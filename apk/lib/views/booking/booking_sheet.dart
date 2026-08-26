import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/dacha_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/custom_button.dart';
import '../profile/auth_modal.dart';

class BookingSheet extends StatefulWidget {
  final DachaModel dacha;

  const BookingSheet({super.key, required this.dacha});

  static Future<void> show(BuildContext context, DachaModel dacha) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => BookingSheet(dacha: dacha),
    );
  }

  @override
  State<BookingSheet> createState() => _BookingSheetState();
}

class _BookingSheetState extends State<BookingSheet> {
  DateTime? _startDate;
  DateTime? _endDate;
  int _guestsCount = 2;
  final TextEditingController _notesController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    // Default 2 days from tomorrow
    final now = DateTime.now();
    _startDate = DateTime(now.year, now.month, now.day).add(const Duration(days: 1));
    _endDate = _startDate!.add(const Duration(days: 2));
    _calculatePrice();
  }

  void _calculatePrice() {
    if (_startDate != null && _endDate != null) {
      final startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
      final endStr = DateFormat('yyyy-MM-dd').format(_endDate!);
      context.read<BookingProvider>().calculatePrice(widget.dacha.id, startStr, endStr);
    }
  }

  Future<void> _selectDateRange() async {
    final now = DateTime.now();
    final picked = await showDateRangePicker(
      context: context,
      firstDate: now,
      lastDate: now.add(const Duration(days: 180)),
      initialDateRange: _startDate != null && _endDate != null
          ? DateTimeRange(start: _startDate!, end: _endDate!)
          : null,
      builder: (context, child) {
        return Theme(
          data: ThemeData.light().copyWith(
            primaryColor: AppColors.primary,
            colorScheme: const ColorScheme.light(primary: AppColors.primary),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
      _calculatePrice();
    }
  }

  Future<void> _submitBooking() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isAuthenticated) {
      AuthModal.show(context, notice: 'Dachani bron qilish uchun avval tizimga kiring.');
      return;
    }

    if (_startDate == null || _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Iltimos, sanalarni tanlang'), backgroundColor: AppColors.error),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
      final endStr = DateFormat('yyyy-MM-dd').format(_endDate!);

      await context.read<BookingProvider>().bookDacha(
            dachaId: widget.dacha.id,
            startDate: startStr,
            endDate: endStr,
            guestsCount: _guestsCount,
            notes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
          );

      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Bron so\'rovingiz yuborildi! Dacha egasi tasdiqlashini kuting.'),
            backgroundColor: AppColors.success,
            duration: Duration(seconds: 4),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bookingProvider = context.watch<BookingProvider>();
    final calc = bookingProvider.calculation;

    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 16),

            Text(
              '🏡 ${widget.dacha.name}',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
            ),
            Text(
              '${widget.dacha.district}, ${widget.dacha.region}',
              style: const TextStyle(fontSize: 13, color: AppColors.textMuted),
            ),
            const SizedBox(height: 18),

            // Date Range Selection Box
            GestureDetector(
              onTap: _selectDateRange,
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.bgPage,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_month_rounded, color: AppColors.primary, size: 24),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Kirish va Chiqish sanalari:', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                          const SizedBox(height: 2),
                          Text(
                            _startDate != null && _endDate != null
                                ? '${DateFormat("dd MMM", "uz").format(_startDate!)} — ${DateFormat("dd MMM", "uz").format(_endDate!)} (${_endDate!.difference(_startDate!).inDays} kun)'
                                : 'Sanalarni tanlang',
                            style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700, color: AppColors.dark),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.edit_calendar_outlined, color: AppColors.textMuted, size: 20),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // Guests Count Row
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Mehmonlar soni', style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700, color: AppColors.dark)),
                    Text('Maksimal: ${widget.dacha.capacity} kishi', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  ],
                ),
                Row(
                  children: [
                    IconButton(
                      onPressed: _guestsCount > 1 ? () => setState(() => _guestsCount--) : null,
                      icon: const Icon(Icons.remove_circle_outline),
                      color: AppColors.primary,
                    ),
                    Text('$_guestsCount', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                    IconButton(
                      onPressed: _guestsCount < widget.dacha.capacity ? () => setState(() => _guestsCount++) : null,
                      icon: const Icon(Icons.add_circle_outline),
                      color: AppColors.primary,
                    ),
                  ],
                ),
              ],
            ),

            const SizedBox(height: 14),

            // Notes input
            TextField(
              controller: _notesController,
              decoration: const InputDecoration(
                hintText: 'Dacha egasiga qo\'shimcha izoh yoki iltimoslar...',
                prefixIcon: Icon(Icons.chat_bubble_outline, color: AppColors.textMuted, size: 20),
              ),
              maxLines: 2,
              style: const TextStyle(fontSize: 13),
            ),

            const SizedBox(height: 16),

            // Live Price Calculation Breakdown
            if (calc != null) ...[
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.primary.withOpacity(0.2)),
                ),
                child: Column(
                  children: [
                    if (calc.weekdaysCount > 0)
                      _buildCalcRow('Ish kunlari (${calc.weekdaysCount} kun)', Formatters.formatCurrency(calc.weekdaysTotal, currency: calc.currency)),
                    if (calc.weekendsCount > 0)
                      _buildCalcRow('Dam olish kunlari (${calc.weekendsCount} kun)', Formatters.formatCurrency(calc.weekendsTotal, currency: calc.currency)),
                    const Divider(height: 14, color: AppColors.border),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Jami summa:', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.dark)),
                        Text(
                          Formatters.formatCurrency(calc.totalPrice, currency: calc.currency),
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppColors.primary),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
            ],

            // Submit Button
            CustomButton(
              text: '✨ Bron so\'rovini yuborish',
              isLoading: _isSubmitting || bookingProvider.isCalculating,
              onPressed: _submitBooking,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCalcRow(String label, String val) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted)),
          Text(val, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.dark)),
        ],
      ),
    );
  }
}
