import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/booking_model.dart';
import '../../services/owner_service.dart';
import 'block_dates_sheet.dart';

class OwnerDashboardScreen extends StatefulWidget {
  const OwnerDashboardScreen({super.key});

  @override
  State<OwnerDashboardScreen> createState() => _OwnerDashboardScreenState();
}

class _OwnerDashboardScreenState extends State<OwnerDashboardScreen> {
  final OwnerService _ownerService = OwnerService();
  List<BookingModel> _bookings = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadOwnerData();
  }

  Future<void> _loadOwnerData() async {
    setState(() => _isLoading = true);
    try {
      _bookings = await _ownerService.getOwnerBookings();
    } catch (e) {
      debugPrint('Error loading owner bookings: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleConfirm(int id) async {
    try {
      await _ownerService.confirmBooking(id);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Bron muvaffaqiyatli tasdiqlandi!'), backgroundColor: AppColors.success),
      );
      _loadOwnerData();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
      );
    }
  }

  Future<void> _handleReject(int id) async {
    try {
      await _ownerService.rejectBooking(id);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Bron so\'rovi rad etildi.'), backgroundColor: AppColors.error),
      );
      _loadOwnerData();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dacha Egasi Paneli 🏡', style: TextStyle(fontWeight: FontWeight.w800)),
      ),
      body: RefreshIndicator(
        onRefresh: _loadOwnerData,
        color: AppColors.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Owner Quick Actions Card
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: const [
                    BoxShadow(color: AppColors.primaryGlow, blurRadius: 16, offset: Offset(0, 6)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Dachangizni boshqaring', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                    const SizedBox(height: 4),
                    const Text('Kelgan so\'rovlarni tasdiqlang yoki sanalarni yopib qo\'ying.', style: TextStyle(color: Colors.white70, fontSize: 13)),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: () {
                        // Open block dates modal for first dacha or select
                        BlockDatesSheet.show(context, 1);
                      },
                      icon: const Icon(Icons.lock_clock, size: 18, color: AppColors.dark),
                      label: const Text('Sanalarni band deb yopish', style: TextStyle(color: AppColors.dark, fontWeight: FontWeight.w700)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),
              const Text('Mijozlardan kelgan bronlar', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark)),
              const SizedBox(height: 12),

              if (_isLoading)
                const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator(color: AppColors.primary)))
              else if (_bookings.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Text('Hozircha hech qanday bron so\'rovi kelmagan.', style: TextStyle(color: AppColors.textMuted)),
                  ),
                )
              else
                ..._bookings.map((b) => _buildOwnerBookingCard(b)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOwnerBookingCard(BookingModel b) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: b.isPending ? AppColors.accent : AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('👤 ${b.user?.name ?? "Mijoz"}', style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800)),
              Text(
                Formatters.formatCurrency(b.totalPrice, currency: b.currency),
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.primary),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text('📞 Tel: ${b.user?.phone ?? "Noma\'lum"}', style: const TextStyle(fontSize: 13, color: AppColors.textMuted)),
          Text('📅 ${b.startDate} — ${b.endDate} (${b.guestsCount} kishi)', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          if (b.notes != null && b.notes!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text('💬 ${b.notes}', style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted, fontStyle: FontStyle.italic)),
            ),
          if (b.isPending) ...[
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => _handleConfirm(b.id),
                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.success, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                    child: const Text('✅ Tasdiqlash', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => _handleReject(b.id),
                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                    child: const Text('❌ Rad etish', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
