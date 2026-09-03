import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/booking_model.dart';
import '../../models/dacha_model.dart';
import '../../models/owner_report_model.dart';
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
  OwnerReportModel? _report;
  
  bool _isLoadingDachas = true;
  bool _isLoadingBookings = true;
  bool _isLoadingReport = true;

  // Report filters
  String _reportPeriod = 'this_month'; // this_month, last_month, this_year, all
  int? _reportDachaId;

  // External / Manual Booking form state
  DachaModel? _selectedBlockDacha;
  DateTime? _blockStartDate;
  DateTime? _blockEndDate;
  String _blockSource = 'telegram'; // telegram, phone, manual
  final TextEditingController _blockPriceController = TextEditingController();
  final TextEditingController _blockCustomerNameController = TextEditingController();
  final TextEditingController _blockCustomerPhoneController = TextEditingController();
  final TextEditingController _blockReasonController = TextEditingController();
  String _blockCurrency = 'USD';
  bool _isBlockingDates = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadAllData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _blockPriceController.dispose();
    _blockCustomerNameController.dispose();
    _blockCustomerPhoneController.dispose();
    _blockReasonController.dispose();
    super.dispose();
  }

  Future<void> _loadAllData() async {
    await Future.wait([
      _loadOwnerDachas(),
      _loadOwnerBookings(),
      _loadOwnerReports(),
    ]);
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
            _blockCurrency = _selectedBlockDacha?.currency ?? 'USD';
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

  Future<void> _loadOwnerReports() async {
    setState(() => _isLoadingReport = true);
    try {
      final report = await _ownerService.getOwnerReports(
        period: _reportPeriod,
        dachaId: _reportDachaId,
      );
      if (mounted) setState(() => _report = report);
    } catch (e) {
      debugPrint('Error loading owner reports: $e');
    } finally {
      if (mounted) setState(() => _isLoadingReport = false);
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
        _loadOwnerReports();
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
        _loadOwnerReports();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _handleDeleteBooking(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Bronni o\'chirish', style: TextStyle(fontWeight: FontWeight.w800)),
        content: const Text('Ushbu tashqi bron yoki yopilgan sanalarni tizimdan butunlay o\'chirmoqchimisiz?'),
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
      await _ownerService.deleteBooking(id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Bron o\'chirildi.'), backgroundColor: AppColors.success),
        );
        _loadOwnerBookings();
        _loadOwnerReports();
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
        _loadOwnerReports();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _handleManualBookingSubmit() async {
    if (_selectedBlockDacha == null || _blockStartDate == null || _blockEndDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Iltimos, dacha va sanalarni tanlang'), backgroundColor: AppColors.error),
      );
      return;
    }

    final price = double.tryParse(_blockPriceController.text.trim()) ?? 0.0;
    setState(() => _isBlockingDates = true);

    try {
      final start = _blockStartDate!.toIso8601String().split('T').first;
      final end = _blockEndDate!.toIso8601String().split('T').first;

      if (_blockSource != 'manual' && price > 0) {
        await _ownerService.createManualBooking(
          dachaId: _selectedBlockDacha!.id,
          startDate: start,
          endDate: end,
          totalPrice: price,
          currency: _blockCurrency,
          source: _blockSource,
          customerName: _blockCustomerNameController.text.trim().isNotEmpty ? _blockCustomerNameController.text.trim() : null,
          customerPhone: _blockCustomerPhoneController.text.trim().isNotEmpty ? _blockCustomerPhoneController.text.trim() : null,
          notes: _blockReasonController.text.trim().isNotEmpty ? _blockReasonController.text.trim() : null,
        );
      } else {
        await _ownerService.blockDates(
          _selectedBlockDacha!.id,
          start,
          end,
          _blockReasonController.text.trim().isNotEmpty ? _blockReasonController.text.trim() : 'Qo\'lda yopilgan',
          totalPrice: price,
          currency: _blockCurrency,
          source: _blockSource,
          customerName: _blockCustomerNameController.text.trim().isNotEmpty ? _blockCustomerNameController.text.trim() : null,
          customerPhone: _blockCustomerPhoneController.text.trim().isNotEmpty ? _blockCustomerPhoneController.text.trim() : null,
        );
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🎉 Tashqi bron / Sanalar saqlandi va hisobotga kiritildi!'), backgroundColor: AppColors.success),
        );
        setState(() {
          _blockStartDate = null;
          _blockEndDate = null;
          _blockPriceController.clear();
          _blockCustomerNameController.clear();
          _blockCustomerPhoneController.clear();
          _blockReasonController.clear();
        });
        _loadOwnerBookings();
        _loadOwnerReports();
        _tabController.animateTo(2); // Hisobotlar tabiga o'tkazish
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
          isScrollable: true,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            Tab(text: '🏡 Dachalar (${_dachas.length})'),
            Tab(text: '📋 Bronlar (${_bookings.length})'),
            const Tab(text: '📊 Hisobotlar & Daromad'),
            const Tab(text: '➕ Tashqi bron / Yopish'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildDachasTab(),
          _buildBookingsTab(),
          _buildReportsTab(),
          _buildManualBookingTab(),
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
                  _loadOwnerReports();
                }
              },
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add_rounded, color: Colors.white),
              label: const Text('Yangi e\'lon', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
            )
          : null,
    );
  }

  // ==========================================
  // TAB 1: DACHALAR
  // ==========================================
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
                            if (result == true) {
                              _loadOwnerDachas();
                              _loadOwnerReports();
                            }
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
                          if (result == true) {
                            _loadOwnerDachas();
                            _loadOwnerReports();
                          }
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
                      onPressed: () async {
                        final res = await BlockDatesSheet.show(context, d.id);
                        if (res == true) {
                          _loadOwnerBookings();
                          _loadOwnerReports();
                        }
                      },
                      icon: const Icon(Icons.lock_clock_outlined, color: AppColors.warning),
                      tooltip: 'Tashqi bron / Yopish',
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

  // ==========================================
  // TAB 2: BRONLAR
  // ==========================================
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
                        Text('Mijozlar dachangizni bron qilishganda yoki o\'zingiz Telegram orqali kiritganingizda shu yerda ko\'rinadi.', textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
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
              Row(
                children: [
                  Text('👤 ${b.clientName}', style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800)),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(b.sourceLabel, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
                  ),
                ],
              ),
              Text(
                Formatters.formatCurrency(b.totalPrice, currency: b.currency),
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.primary),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text('🏠 Dacha: ${b.dacha?.name ?? "Dacha"}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.dark)),
          Text('📞 Tel: ${b.clientPhone}', style: const TextStyle(fontSize: 13, color: AppColors.textMuted)),
          Text('📅 ${b.startDate} — ${b.endDate} (${b.guestsCount} kishi)', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          if (b.notes != null && b.notes!.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text('💬 ${b.notes}', style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted, fontStyle: FontStyle.italic)),
            ),
          const SizedBox(height: 8),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: b.isConfirmed ? AppColors.success.withOpacity(0.12) : (b.isPending ? AppColors.warning.withOpacity(0.12) : AppColors.error.withOpacity(0.12)),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  b.isConfirmed ? '● Tasdiqlangan' : (b.isPending ? '● Kutilmoqda' : '● Bekor qilingan'),
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w800,
                    color: b.isConfirmed ? AppColors.success : (b.isPending ? AppColors.warning : AppColors.error),
                  ),
                ),
              ),

              if (b.source != 'app' || b.isCancelled)
                IconButton(
                  onPressed: () => _handleDeleteBooking(b.id),
                  icon: const Icon(Icons.delete_outline, size: 20, color: AppColors.textMuted),
                  tooltip: 'O\'chirish',
                ),
            ],
          ),

          if (b.isPending) ...[
            const SizedBox(height: 10),
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

  // ==========================================
  // TAB 3: HISOBOTLAR & DAROMAD (REPORTS)
  // ==========================================
  Widget _buildReportsTab() {
    if (_isLoadingReport) {
      return const Center(child: CircularProgressIndicator(color: AppColors.primary));
    }

    final rep = _report;
    if (rep == null) {
      return const Center(child: Text('Hisobot yuklanmadi'));
    }

    final sum = rep.summary;

    return RefreshIndicator(
      onRefresh: _loadOwnerReports,
      color: AppColors.primary,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Period Filter Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('📊 Moliyaviy Hisobot', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark)),
                    Text('Davr: ${rep.periodLabel}', style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  ],
                ),
                DropdownButton<String>(
                  value: _reportPeriod,
                  underline: const SizedBox(),
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.primary),
                  items: const [
                    DropdownMenuItem(value: 'this_month', child: Text('Shu oy')),
                    DropdownMenuItem(value: 'last_month', child: Text('O\'tgan oy')),
                    DropdownMenuItem(value: 'this_year', child: Text('Shu yil')),
                    DropdownMenuItem(value: 'all', child: Text('Barcha vaqt')),
                  ],
                  onChanged: (val) {
                    if (val != null) {
                      setState(() => _reportPeriod = val);
                      _loadOwnerReports();
                    }
                  },
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Income Cards (UZS & USD)
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    title: 'Jami daromad (USD)',
                    value: '\$${sum.totalIncomeUsd.toStringAsFixed(0)}',
                    icon: Icons.attach_money_rounded,
                    color: const Color(0xFF10B981),
                    bgColor: const Color(0xFFECFDF5),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    title: 'Jami daromad (UZS)',
                    value: '${Formatters.formatNumber(sum.totalIncomeUzs)} so\'m',
                    icon: Icons.account_balance_wallet_rounded,
                    color: const Color(0xFF3B82F6),
                    bgColor: const Color(0xFFEFF6FF),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    title: 'Band kunlar soni',
                    value: '${sum.totalBookedDays} kun',
                    subtext: 'Bandlik: ${sum.occupancyRate}%',
                    icon: Icons.calendar_month_rounded,
                    color: const Color(0xFFF59E0B),
                    bgColor: const Color(0xFFFEF3C7),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    title: 'Tasdiqlangan bronlar',
                    value: '${sum.confirmedBookings} ta',
                    subtext: 'Jami: ${sum.totalBookings} ta so\'rov',
                    icon: Icons.check_circle_outline_rounded,
                    color: const Color(0xFF8B5CF6),
                    bgColor: const Color(0xFFF5F3FF),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // Manbalar taqsimoti (Telegram vs Dastur vs Qo'lda)
            const Text('📱 Bronlar va Daromad Manbalari', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark)),
            const SizedBox(height: 6),
            const Text('Telegram yoki dastur orqali qancha daromad qilganingiz tahlili:', style: TextStyle(fontSize: 12.5, color: AppColors.textMuted)),
            const SizedBox(height: 12),

            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: rep.sources.map((s) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Row(
                      children: [
                        Text(s.icon, style: const TextStyle(fontSize: 24)),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(s.label, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
                              Text('${s.count} ta bron', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            if (s.incomeUsd > 0)
                              Text('\$${s.incomeUsd.toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF10B981))),
                            if (s.incomeUzs > 0)
                              Text('${Formatters.formatNumber(s.incomeUzs)} so\'m', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF3B82F6), fontSize: 12)),
                            if (s.incomeUsd == 0 && s.incomeUzs == 0)
                              const Text('0', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                          ],
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),

            const SizedBox(height: 24),

            // Oylik daromad dinamikasi
            if (rep.monthlyTrend.isNotEmpty) ...[
              const Text('📈 Oylik Daromad Dinamikasi', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark)),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: rep.monthlyTrend.map((m) {
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(m.monthName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
                          Row(
                            children: [
                              Text('${m.bookingsCount} bron', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                              const SizedBox(width: 12),
                              Text(
                                m.incomeUsd > 0 ? '\$${m.incomeUsd.toStringAsFixed(0)}' : (m.incomeUzs > 0 ? '${Formatters.formatNumber(m.incomeUzs)} so\'m' : '0'),
                                style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.primary),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],

            const SizedBox(height: 24),

            // Dachalar kesimida
            if (rep.dachasBreakdown.isNotEmpty) ...[
              const Text('🏡 Dachalar Kesimida Daromad', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark)),
              const SizedBox(height: 12),
              ...rep.dachasBreakdown.map((d) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(d.name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                            Text('${d.bookingsCount} ta muvaffaqiyatli bron', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                          ],
                        ),
                      ),
                      Text(
                        d.incomeUsd > 0 ? '\$${d.incomeUsd.toStringAsFixed(0)}' : '${Formatters.formatNumber(d.incomeUzs)} so\'m',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5, color: AppColors.primary),
                      ),
                    ],
                  ),
                );
              }),
            ],

            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    String? subtext,
    required IconData icon,
    required Color color,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(child: Text(title, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.textMuted), maxLines: 1, overflow: TextOverflow.ellipsis)),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(10)),
                child: Icon(icon, size: 16, color: color),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900, color: color)),
          if (subtext != null) ...[
            const SizedBox(height: 2),
            Text(subtext, style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // TAB 4: TASHQI BRON KIRITISH & BAND QILISH
  // ==========================================
  Widget _buildManualBookingTab() {
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
                const Text('➕ Tashqi bron kiritish / Sanalarni yopish', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.dark)),
                const SizedBox(height: 6),
                const Text(
                  'Telegram, telefon orqali kelishilgan yoki ta\'mirdagi kunlarni kiritib, daromad hisobotingizni 100% to\'g\'ri yuriting.',
                  style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                ),
                const SizedBox(height: 20),

                // Source Chips
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
                const SizedBox(height: 16),

                // Select dacha
                const Text('Dachani tanlang *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                const SizedBox(height: 6),
                if (_dachas.isNotEmpty)
                  DropdownButtonFormField<DachaModel>(
                    value: _selectedBlockDacha ?? _dachas.first,
                    items: _dachas.map((d) => DropdownMenuItem(value: d, child: Text(d.name, style: const TextStyle(fontSize: 14)))).toList(),
                    onChanged: (v) {
                      if (v != null) {
                        setState(() {
                          _selectedBlockDacha = v;
                          _blockCurrency = v.currency ?? 'USD';
                        });
                      }
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
                const Text('Band qilinadigan sanalar *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                const SizedBox(height: 6),
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

                // Price & Currency
                Row(
                  children: [
                    Expanded(
                      flex: 3,
                      child: TextField(
                        controller: _blockPriceController,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'Kelishilgan narx',
                          hintText: 'Masalan: 1500000',
                          prefixIcon: const Icon(Icons.payments_outlined, size: 20),
                          filled: true,
                          fillColor: AppColors.bgPage,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      flex: 2,
                      child: DropdownButtonFormField<String>(
                        value: _blockCurrency,
                        items: const [
                          DropdownMenuItem(value: 'USD', child: Text('USD (\$)')),
                          DropdownMenuItem(value: 'UZS', child: Text('UZS (so\'m)')),
                        ],
                        onChanged: (v) => setState(() => _blockCurrency = v ?? 'USD'),
                        decoration: InputDecoration(
                          labelText: 'Valyuta',
                          filled: true,
                          fillColor: AppColors.bgPage,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // Customer Name & Phone
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _blockCustomerNameController,
                        decoration: InputDecoration(
                          labelText: 'Mijoz ismi (Ixtiyoriy)',
                          hintText: 'Masalan: Dilshod',
                          prefixIcon: const Icon(Icons.person_outline, size: 20),
                          filled: true,
                          fillColor: AppColors.bgPage,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: _blockCustomerPhoneController,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          labelText: 'Telefon',
                          hintText: '+998901234567',
                          prefixIcon: const Icon(Icons.phone_outlined, size: 20),
                          filled: true,
                          fillColor: AppColors.bgPage,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                TextField(
                  controller: _blockReasonController,
                  decoration: InputDecoration(
                    labelText: 'Izoh / Qo\'shimcha ma\'lumot',
                    hintText: 'Masalan: Telegram guruh orqali bron qilindi',
                    prefixIcon: const Icon(Icons.notes_outlined, size: 20),
                    filled: true,
                    fillColor: AppColors.bgPage,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                  ),
                ),

                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _isBlockingDates ? null : _handleManualBookingSubmit,
                    icon: _isBlockingDates
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.save_rounded, size: 18, color: Colors.white),
                    label: Text(
                      _isBlockingDates ? 'Saqlanmoqda...' : '💾 Saqlash va Hisobotga kiritish',
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

  Widget _buildSourceChip(String key, String label) {
    final isSelected = _blockSource == key;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _blockSource = key),
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
