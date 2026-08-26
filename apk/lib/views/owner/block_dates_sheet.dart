import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/constants/app_colors.dart';
import '../../services/owner_service.dart';
import '../../widgets/custom_button.dart';

class BlockDatesSheet extends StatefulWidget {
  final int dachaId;

  const BlockDatesSheet({super.key, required this.dachaId});

  static Future<void> show(BuildContext context, int dachaId) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => BlockDatesSheet(dachaId: dachaId),
    );
  }

  @override
  State<BlockDatesSheet> createState() => _BlockDatesSheetState();
}

class _BlockDatesSheetState extends State<BlockDatesSheet> {
  DateTime? _startDate;
  DateTime? _endDate;
  final TextEditingController _reasonController = TextEditingController(text: 'Dacha egasi tomonidan yopilgan');
  bool _isLoading = false;

  Future<void> _selectDateRange() async {
    final now = DateTime.now();
    final picked = await showDateRangePicker(
      context: context,
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );

    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
    }
  }

  Future<void> _submitBlockDates() async {
    if (_startDate == null || _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Iltimos, sanalarni tanlang'), backgroundColor: AppColors.error),
      );
      return;
    }

    setState(() => _isLoading = true);
    try {
      final startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
      final endStr = DateFormat('yyyy-MM-dd').format(_endDate!);
      await OwnerService().blockDates(
        widget.dachaId,
        startStr,
        endStr,
        _reasonController.text.trim(),
      );

      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Sanalar muvaffaqiyatli band qilib yopildi!'), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
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
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(10))),
          ),
          const SizedBox(height: 16),
          const Text(
            '🔒 Sanalarni qo\'lda "Band" deb yopish',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
          ),
          const SizedBox(height: 4),
          const Text(
            'Boshqa manbalar orqali bron qilingan yoki ta\'mirdagi kunlarni kalendarda yoping.',
            style: TextStyle(fontSize: 12.5, color: AppColors.textMuted),
          ),
          const SizedBox(height: 18),

          GestureDetector(
            onTap: _selectDateRange,
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.bgPage,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  const Icon(Icons.calendar_month, color: AppColors.primary),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      _startDate != null && _endDate != null
                          ? '${DateFormat("dd.MM.yyyy").format(_startDate!)} — ${DateFormat("dd.MM.yyyy").format(_endDate!)}'
                          : 'Sanalarni tanlash uchun bosing',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 14),

          TextField(
            controller: _reasonController,
            decoration: const InputDecoration(
              hintText: 'Yopish sababi (Masalan: Ta\'mir yoki qo\'lda berilgan)',
            ),
          ),
          const SizedBox(height: 20),

          CustomButton(
            text: '🔒 Sanalarni yopish',
            isLoading: _isLoading,
            onPressed: _submitBlockDates,
          ),
        ],
      ),
    );
  }
}
