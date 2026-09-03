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
  String _source = 'telegram'; // telegram, phone, manual
  final TextEditingController _priceController = TextEditingController();
  final TextEditingController _customerNameController = TextEditingController();
  final TextEditingController _customerPhoneController = TextEditingController();
  final TextEditingController _reasonController = TextEditingController();
  String _currency = 'USD';
  bool _isLoading = false;

  @override
  void dispose() {
    _priceController.dispose();
    _customerNameController.dispose();
    _customerPhoneController.dispose();
    _reasonController.dispose();
    super.dispose();
  }

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

    final price = double.tryParse(_priceController.text.trim()) ?? 0.0;

    setState(() => _isLoading = true);
    try {
      final startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
      final endStr = DateFormat('yyyy-MM-dd').format(_endDate!);
      
      if (_source != 'manual' && price > 0) {
        // Tashqi bron sifatida kiritish (Hisobotga tushadi)
        await OwnerService().createManualBooking(
          dachaId: widget.dachaId,
          startDate: startStr,
          endDate: endStr,
          totalPrice: price,
          currency: _currency,
          source: _source,
          customerName: _customerNameController.text.trim().isNotEmpty ? _customerNameController.text.trim() : null,
          customerPhone: _customerPhoneController.text.trim().isNotEmpty ? _customerPhoneController.text.trim() : null,
          notes: _reasonController.text.trim().isNotEmpty ? _reasonController.text.trim() : null,
        );
      } else {
        // Shunchaki yopish
        await OwnerService().blockDates(
          widget.dachaId,
          startStr,
          endStr,
          _reasonController.text.trim().isNotEmpty ? _reasonController.text.trim() : 'Qo\'lda yopilgan',
          totalPrice: price,
          currency: _currency,
          source: _source,
          customerName: _customerNameController.text.trim().isNotEmpty ? _customerNameController.text.trim() : null,
          customerPhone: _customerPhoneController.text.trim().isNotEmpty ? _customerPhoneController.text.trim() : null,
        );
      }

      if (mounted) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🎉 Bron / Band sanalar muvaffaqiyatli saqlandi!'), backgroundColor: AppColors.success),
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
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(10))),
            ),
            const SizedBox(height: 16),
            const Text(
              '🔒 Tashqi bron / Sanalarni band qilish',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
            ),
            const SizedBox(height: 4),
            const Text(
              'Telegram yoki telefon orqali band qilingan dachani kiritib, daromad hisobotini to\'g\'ri yuriting.',
              style: TextStyle(fontSize: 12.5, color: AppColors.textMuted),
            ),
            const SizedBox(height: 16),

            // Source Selector (Chips)
            const Text('Bron manbasi *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            Row(
              children: [
                _buildSourceChip('telegram', 'Telegram 📱'),
                const SizedBox(width: 8),
                _buildSourceChip('phone', 'Telefon 📞'),
                const SizedBox(width: 8),
                _buildSourceChip('manual', 'Yopish 🚫'),
              ],
            ),
            const SizedBox(height: 14),

            // Date Picker
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
                            : 'Band qilinadigan sanalarni tanlang *',
                        style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: _startDate != null ? AppColors.dark : AppColors.textMuted),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 14),

            // Price & Currency
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: TextField(
                    controller: _priceController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Kelishilgan narx',
                      hintText: 'Masalan: 1500000',
                      prefixIcon: Icon(Icons.payments_outlined, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 2,
                  child: DropdownButtonFormField<String>(
                    value: _currency,
                    items: const [
                      DropdownMenuItem(value: 'USD', child: Text('USD (\$)')),
                      DropdownMenuItem(value: 'UZS', child: Text('UZS (so\'m)')),
                    ],
                    onChanged: (v) => setState(() => _currency = v ?? 'USD'),
                    decoration: const InputDecoration(labelText: 'Valyuta'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Customer Name & Phone
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _customerNameController,
                    decoration: const InputDecoration(
                      labelText: 'Mijoz ismi (Ixtiyoriy)',
                      hintText: 'Masalan: Alisher',
                      prefixIcon: Icon(Icons.person_outline, size: 20),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: _customerPhoneController,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Telefon raqam',
                      hintText: '+998901234567',
                      prefixIcon: Icon(Icons.phone_outlined, size: 20),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            TextField(
              controller: _reasonController,
              decoration: const InputDecoration(
                labelText: 'Izoh / Qo\'shimcha ma\'lumot',
                hintText: 'Masalan: Avans 500 ming berildi yoki Telegram guruh orqali',
                prefixIcon: Icon(Icons.notes_outlined, size: 20),
              ),
            ),
            const SizedBox(height: 20),

            CustomButton(
              text: '💾 Saqlash va Hisobotga kiritish',
              isLoading: _isLoading,
              onPressed: _submitBlockDates,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSourceChip(String key, String label) {
    final isSelected = _source == key;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _source = key),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary : AppColors.bgPage,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isSelected ? AppColors.primary : AppColors.border),
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: isSelected ? Colors.white : AppColors.dark,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

