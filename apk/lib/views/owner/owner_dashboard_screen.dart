import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/booking_model.dart';
import '../../models/dacha_model.dart';
import '../../services/owner_service.dart';
import '../explore/dacha_detail_screen.dart';
import 'block_dates_sheet.dart';
import 'create_edit_dacha_screen.dart';

class OwnerDashboardScreen extends StatefulWidget {
  const OwnerDashboardScreen({super.key});

  @override
  State<OwnerDashboardScreen> createState() => _OwnerDashboardScreenState();
}

class _OwnerDashboardScreenState extends State<OwnerDashboardScreen> with SingleTickerProviderStateMixin {
  final OwnerService _ownerService = OwnerService();
  late TabController _tabController;

  List<DachaModel> _dachas = [];
  List<BookingModel> _bookings = [];
  bool _isLoadingDachas = true;
  bool _isLoadingBookings = true;

  // Block dates form state
  DachaModel? _selectedBlockDacha;
  DateTime? _blockStartDate;
  DateTime? _blockEndDate;
  final TextEditingController _blockReasonController = TextEditingController();
  bool _isBlockingDates = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadAllData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _blockReasonController.dispose();
    super.dispose();
  }

  Future<void> _loadAllData() async {
    await Future.wait([_loadOwnerDachas(), _loadOwnerBookings()]);
  }

  Future<void> _loadOwnerDachas() async {
    setState(() => _isLoadingDachas = true);
    try {
      final dachas = await _ownerService.getOwnerDachas();
      if (mounted) {
        setState(() {
          _dachas = dachas;
          if (_dachas.isNotEmpty && _selectedBlockDacha == null) {
            _selectedBlockDacha = _dachas.first;
          }
        });
      }
    } catch (e) {
      debugPrint('Error loading owner dachas: $e');
    } finally {
      if (mounted) setState(() => _isLoadingDachas = false);
    }
  }

  Future<void> _loadOwnerBookings() async {
    setState(() => _isLoadingBookings = true);
    try {
      final bookings = await _ownerService.getOwnerBookings();
      if (mounted) setState(() => _bookings = bookings);
    } catch (e) {
      debugPrint('Error loading owner bookings: $e');
    } finally {
      if (mounted) setState(() => _isLoadingBookings = false);
    }
  }

  Future<void> _handleConfirm(int id) async {
    try {
      await _ownerService.confirmBooking(id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🎉 Bron muvaffaqiyatli tasdiqlandi!'), backgroundColor: AppColors.success),
        );
        _loadOwnerBookings();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _handleReject(int id) async {
    try {
      await _ownerService.rejectBooking(id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Bron so\'rovi rad etildi.'), backgroundColor: AppColors.error),
        );
        _loadOwnerBookings();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _handleDeleteDacha(DachaModel dacha) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('E\'lonni o\'chirish', style: TextStyle(fontWeight: FontWeight.w800)),
        content: Text('Haqiqatan ham "${dacha.name}" dacha e\'lonini o\'chirmoqchimisiz?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Bekor qilish', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('O\'chirish', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      await _ownerService.deleteDacha(dacha.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Dacha muvaffaqiyatli o\'chirildi.'), backgroundColor: AppColors.success),
        );
        _loadOwnerDachas();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _handleBlockDatesSubmit() async {
    if (_selectedBlockDacha == null || _blockStartDate == null || _blockEndDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Iltimos, dacha va sanalarni tanlang'), backgroundColor: AppColors.error),
      );
      return;
    }

    setState(() => _isBlockingDates = true);

    try {
      final start = _blockStartDate!.toIso8601String().split('T').first;
      final end = _blockEndDate!.toIso8601String().split('T').first;
      await _ownerService.blockDates(
        _selectedBlockDacha!.id,
        start,
        end,
        _blockReasonController.text.trim().isNotEmpty ? _blockReasonController.text.trim() : null,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🔒 Sanalar muvaffaqiyatli band qilib yopildi!'), backgroundColor: AppColors.success),
        );
        setState(() {
          _blockStartDate = null;
          _blockEndDate = null;
          _blockReasonController.clear();
        });
        _tabController.animateTo(0);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isBlockingDates = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dacha Egasi Kabineti 🗂️', style: TextStyle(fontWeight: FontWeight.w800)),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            Tab(text: '🏡 Dachalar (${_dachas.length})'),
            Tab(text: '📋 Bronlar (${_bookings.length})'),
            const Tab(text: '🚫 Sanalarni yopish'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildDachasTab(),
          _buildBookingsTab(),
          _buildBlockDatesTab(),
        ],
      ),
      floatingActionButton: _tabController.index == 0
          ? FloatingActionButton.extended(
              onPressed: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const CreateEditDachaScreen()),
                );
                if (result == true) {
                  _loadOwnerDachas();
                }
              },
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add_rounded, color: Colors.white),
              label: const Text('Yangi e\'lon', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
            )
          : null,
    );
  }

  Widget _buildDachasTab() {
    return RefreshIndicator(
      onRefresh: _loadOwnerDachas,
      color: AppColors.primary,
      child: _isLoadingDachas
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _dachas.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Text('🏡', style: TextStyle(fontSize: 54)),
                        const SizedBox(height: 12),
                        const Text('Hozircha e\'lonlaringiz yo\'q', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 6),
                        const Text('Yangi dacha e\'lonini joylashtiring va mijozlar qabul qiling.', textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                        const SizedBox(height: 20),
                        ElevatedButton.icon(
                          onPressed: () async {
                            final result = await Navigator.push(
                              context,
                              MaterialPageRoute(builder: (context) => const CreateEditDachaScreen()),
                            );
                            if (result == true) _loadOwnerDachas();
                          },
                          icon: const Icon(Icons.add_rounded, color: Colors.white),
                          label: const Text('Yangi e\'lon joylash', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
                  itemCount: _dachas.length,
                  itemBuilder: (context, index) {
                    final d = _dachas[index];
                    return _buildOwnerDachaCard(d);
                  },
                ),
    );
  }

  Widget _buildOwnerDachaCard(DachaModel d) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 12, offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image with status badge
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                child: Image.network(
                  d.firstImageUrl,
                  height: 160,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    height: 160,
                    color: AppColors.bgPage,
                    child: const Center(child: Icon(Icons.broken_image_rounded, size: 40, color: AppColors.textMuted)),
                  ),
                ),
              ),
              Positioned(
                top: 12,
                left: 12,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: d.status == 'active' ? AppColors.success.withOpacity(0.9) : AppColors.warning.withOpacity(0.9),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    d.status == 'active' ? '● Faol' : '● Kutilmoqda',
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            ],
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(d.name, style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: AppColors.dark)),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 14, color: AppColors.textMuted),
                    const SizedBox(width: 4),
                    Text('${d.region}, ${d.district}', style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted)),
                  ],
                ),
                const SizedBox(height: 8),
                Text('👥 ${d.capacity} kishilik • 🛏️ ${d.roomsCount} xona', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                const SizedBox(height: 10),

                // Prices
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(color: AppColors.bgPage, borderRadius: BorderRadius.circular(10)),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Ish kunlari: ${Formatters.formatCurrency(d.weekdayPrice, currency: d.currency)}', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                      Text('Dam olish: ${Formatters.formatCurrency(d.weekendPrice, currency: d.currency)}', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.accent)),
                    ],
                  ),
                ),

                const SizedBox(height: 14),
                const Divider(height: 1, color: AppColors.border),
                const SizedBox(height: 10),

                // Action Buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => DachaDetailScreen(dachaId: d.id)),
                          );
                        },
                        icon: const Icon(Icons.visibility_outlined, size: 16),
                        label: const Text('Ko\'rish', style: TextStyle(fontSize: 12.5)),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final result = await Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => CreateEditDachaScreen(dacha: d)),
                          );
                          if (result == true) _loadOwnerDachas();
                        },
                        icon: const Icon(Icons.edit_outlined, size: 16, color: AppColors.primary),
                        label: const Text('Tahrirlash', style: TextStyle(fontSize: 12.5, color: AppColors.primary)),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          side: const BorderSide(color: AppColors.primaryLight),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      onPressed: () => BlockDatesSheet.show(context, d.id),
                      icon: const Icon(Icons.lock_clock_outlined, color: AppColors.warning),
                      tooltip: 'Sanalarni yopish',
                    ),
                    IconButton(
                      onPressed: () => _handleDeleteDacha(d),
                      icon: const Icon(Icons.delete_outline_rounded, color: AppColors.error),
                      tooltip: 'O\'chirish',
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBookingsTab() {
    return RefreshIndicator(
      onRefresh: _loadOwnerBookings,
      color: AppColors.primary,
      child: _isLoadingBookings
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _bookings.isEmpty
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text('📋', style: TextStyle(fontSize: 48)),
                        SizedBox(height: 12),
                        Text('Hozircha bron so\'rovlari yo\'q', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                        SizedBox(height: 4),
                        Text('Mijozlar dachangizni bron qilishganda barcha so\'rovlar shu yerda ko\'rinadi.', textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                      ],
                    ),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _bookings.length,
                  itemBuilder: (context, index) {
                    final b = _bookings[index];
                    return _buildOwnerBookingCard(b);
                  },
                ),
    );
  }

  Widget _buildOwnerBookingCard(BookingModel b) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
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
          const SizedBox(height: 4),
          Text('📞 Tel: ${b.user?.phone ?? "Kiritilmagan"}', style: const TextStyle(fontSize: 13, color: AppColors.textMuted)),
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

  Widget _buildBlockDatesTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: AppColors.border),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('🚫 Sanalarni band deb yopish', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.dark)),
                const SizedBox(height: 6),
                const Text(
                  'Dachangizni ta\'mirlash yoki shaxsiy dam olish sababli ma\'lum sanalarga yopib qo\'ying.',
                  style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                ),
                const SizedBox(height: 20),

                // Select dacha
                const Text('Dachani tanlang *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                const SizedBox(height: 6),
                if (_dachas.isNotEmpty)
                  DropdownButtonFormField<DachaModel>(
                    value: _selectedBlockDacha ?? _dachas.first,
                    items: _dachas.map((d) => DropdownMenuItem(value: d, child: Text(d.name, style: const TextStyle(fontSize: 14)))).toList(),
                    onChanged: (v) {
                      if (v != null) setState(() => _selectedBlockDacha = v);
                    },
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: AppColors.bgPage,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  )
                else
                  const Text('Dachalar mavjud emas. Avval e\'lon joylang.', style: TextStyle(color: AppColors.error, fontSize: 13)),

                const SizedBox(height: 16),

                // Date pickers
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) setState(() => _blockStartDate = picked);
                        },
                        icon: const Icon(Icons.calendar_today, size: 16),
                        label: Text(
                          _blockStartDate != null ? _blockStartDate!.toIso8601String().split('T').first : 'Boshlanish',
                          style: const TextStyle(fontSize: 13),
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _blockStartDate ?? DateTime.now(),
                            firstDate: _blockStartDate ?? DateTime.now(),
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) setState(() => _blockEndDate = picked);
                        },
                        icon: const Icon(Icons.event, size: 16),
                        label: Text(
                          _blockEndDate != null ? _blockEndDate!.toIso8601String().split('T').first : 'Tugash',
                          style: const TextStyle(fontSize: 13),
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),
                const Text('Sabab (Ixtiyoriy)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                const SizedBox(height: 6),
                TextField(
                  controller: _blockReasonController,
                  decoration: InputDecoration(
                    hintText: 'Masalan: Ta\'mirlash yoki o\'zimiz dam olamiz',
                    hintStyle: const TextStyle(fontSize: 13, color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.bgPage,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                  ),
                ),

                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _isBlockingDates ? null : _handleBlockDatesSubmit,
                    icon: _isBlockingDates
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.lock_rounded, size: 18, color: Colors.white),
                    label: Text(
                      _isBlockingDates ? 'Yopilmoqda...' : '🔒 Sanalarni yopish',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 14),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
