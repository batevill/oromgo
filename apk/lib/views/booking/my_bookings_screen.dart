import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/booking_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/custom_button.dart';
import '../profile/auth_modal.dart';

class MyBookingsScreen extends StatefulWidget {
  const MyBookingsScreen({super.key});

  @override
  State<MyBookingsScreen> createState() => _MyBookingsScreenState();
}

class _MyBookingsScreenState extends State<MyBookingsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    Future.microtask(() {
      if (context.read<AuthProvider>().isAuthenticated) {
        context.read<BookingProvider>().fetchMyBookings();
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  List<BookingModel> _filterBookings(List<BookingModel> list, int tabIndex) {
    if (tabIndex == 1) return list.where((b) => b.isConfirmed).toList();
    if (tabIndex == 2) return list.where((b) => b.isPending).toList();
    if (tabIndex == 3) return list.where((b) => b.isCancelled).toList();
    return list;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final bookingProvider = context.watch<BookingProvider>();

    if (!auth.isAuthenticated) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mening Bronlarim')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('📅', style: TextStyle(fontSize: 56)),
                const SizedBox(height: 16),
                const Text(
                  'Bronlaringizni ko\'rish uchun kiring',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Barcha amalga oshirilgan bronlar va ularning statuslarini boshqaring.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 13.5, color: AppColors.textMuted),
                ),
                const SizedBox(height: 24),
                CustomButton(
                  text: 'Tizimga kirish',
                  onPressed: () => AuthModal.show(context),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mening Bronlarim 📅', style: TextStyle(fontWeight: FontWeight.w800)),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
          tabs: const [
            Tab(text: 'Barchasi'),
            Tab(text: 'Tasdiqlangan'),
            Tab(text: 'Kutilmoqda'),
            Tab(text: 'Bekor qilingan'),
          ],
        ),
      ),
      body: bookingProvider.isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : TabBarView(
              controller: _tabController,
              children: List.generate(4, (index) {
                final filtered = _filterBookings(bookingProvider.myBookings, index);
                if (filtered.isEmpty) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('📭', style: TextStyle(fontSize: 48)),
                          SizedBox(height: 12),
                          Text('Ushbu toifada bronlar yo\'q', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.dark)),
                        ],
                      ),
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () => bookingProvider.fetchMyBookings(),
                  color: AppColors.primary,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(20),
                    itemCount: filtered.length,
                    itemBuilder: (context, idx) {
                      final booking = filtered[idx];
                      return _buildBookingCard(context, booking);
                    },
                  ),
                );
              }),
            ),
    );
  }

  Widget _buildBookingCard(BuildContext context, BookingModel booking) {
    Color statusBg = AppColors.accentLight;
    Color statusColor = AppColors.warning;
    String statusLabel = '⏳ Kutilmoqda';

    if (booking.isConfirmed) {
      statusBg = AppColors.successLight;
      statusColor = AppColors.success;
      statusLabel = '✅ Tasdiqlangan';
    } else if (booking.isCancelled) {
      statusBg = AppColors.errorLight;
      statusColor = AppColors.error;
      statusLabel = '❌ Bekor qilingan';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  booking.dacha?.name ?? 'Dacha #${booking.dachaId}',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(12)),
                child: Text(
                  statusLabel,
                  style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: statusColor),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1, color: AppColors.border),
          const SizedBox(height: 10),
          Row(
            children: [
              const Icon(Icons.calendar_today_outlined, size: 16, color: AppColors.primary),
              const SizedBox(width: 6),
              Text(
                '${booking.startDate} — ${booking.endDate}',
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppColors.dark),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('👥 ${booking.guestsCount} kishi', style: const TextStyle(fontSize: 13, color: AppColors.textMuted)),
              Text(
                Formatters.formatCurrency(booking.totalPrice, currency: booking.currency),
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.primary),
              ),
            ],
          ),
          if (booking.isPending) ...[
            const SizedBox(height: 12),
            OutlinedButton(
              onPressed: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: const Text('Bronni bekor qilish'),
                    content: const Text('Haqiqatan ham ushbu bron so\'rovingizni bekor qilmoqchimisiz?'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Yo\'q')),
                      TextButton(
                        onPressed: () => Navigator.pop(ctx, true),
                        child: const Text('Ha, bekor qilish', style: TextStyle(color: AppColors.error)),
                      ),
                    ],
                  ),
                );

                if (confirm == true && context.mounted) {
                  await context.read<BookingProvider>().cancelBooking(booking.id);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Bron bekor qilindi.'), backgroundColor: AppColors.success),
                  );
                }
              },
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.error,
                side: const BorderSide(color: AppColors.errorLight),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                minimumSize: const Size(double.infinity, 36),
              ),
              child: const Text('Bronni bekor qilish', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
            ),
          ],
        ],
      ),
    );
  }
}
