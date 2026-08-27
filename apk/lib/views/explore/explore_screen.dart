import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../providers/auth_provider.dart';
import '../../providers/dacha_provider.dart';
import '../../widgets/category_pill.dart';
import '../../widgets/dacha_card.dart';
import '../../widgets/location_filter_modal.dart';
import '../../widgets/search_bar_widget.dart';
import '../../widgets/server_config_dialog.dart';
import '../profile/auth_modal.dart';
import 'dacha_detail_screen.dart';

class ExploreScreen extends StatefulWidget {
  const ExploreScreen({super.key});

  @override
  State<ExploreScreen> createState() => _ExploreScreenState();
}

class _ExploreScreenState extends State<ExploreScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      final provider = context.read<DachaProvider>();
      provider.fetchDachas();
      provider.fetchAmenities();
      provider.fetchLocations();
    });
  }

  void _showFilterModal() {
    LocationFilterModal.show(context);
  }

  @override
  Widget build(BuildContext context) {
    final dachaProvider = context.watch<DachaProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Text('🏡', style: TextStyle(fontSize: 18)),
            ),
            const SizedBox(width: 8),
            const Text('Oromgo', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: AppColors.dark)),
          ],
        ),
        actions: [
          IconButton(
            icon: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: AppColors.primaryLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.dns_outlined, color: AppColors.primary, size: 20),
            ),
            tooltip: 'Server Sozlamalari (API URL)',
            onPressed: () => ServerConfigDialog.show(context),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => dachaProvider.fetchDachas(),
        color: AppColors.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 10),

              // Search Bar
              SearchBarWidget(
                onTapFilter: _showFilterModal,
                onSearch: (q) {
                  dachaProvider.setSearchQuery(q.trim().isEmpty ? null : q.trim());
                },
              ),
              const SizedBox(height: 12),

              // Active Location Filter Badge / Indicator
              if (dachaProvider.hasActiveLocationFilter) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: AppColors.primaryLight,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.place_rounded, size: 16, color: AppColors.primary),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          dachaProvider.activeLocationLabel,
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.primary),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      InkWell(
                        onTap: () => dachaProvider.clearFilters(),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Icon(Icons.cancel_rounded, size: 18, color: AppColors.primary),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
              ],

              // Category Pills Carousel
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    CategoryPill(
                      label: 'Barchasi',
                      icon: '🏡',
                      isSelected: dachaProvider.selectedCategory == 'all',
                      onTap: () => dachaProvider.setCategory('all'),
                    ),
                    const SizedBox(width: 8),
                    CategoryPill(
                      label: 'Basseynli',
                      icon: '🏊‍♂️',
                      isSelected: dachaProvider.selectedCategory == 'pool',
                      onTap: () => dachaProvider.setCategory('pool'),
                    ),
                    const SizedBox(width: 8),
                    CategoryPill(
                      label: 'Sauna / Hammom',
                      icon: '🧖‍♂️',
                      isSelected: dachaProvider.selectedCategory == 'sauna',
                      onTap: () => dachaProvider.setCategory('sauna'),
                    ),
                    const SizedBox(width: 8),
                    CategoryPill(
                      label: 'Tog\' manzarasi',
                      icon: '🏔️',
                      isSelected: dachaProvider.selectedCategory == 'mountain',
                      onTap: () => dachaProvider.setCategory('mountain'),
                    ),
                    const SizedBox(width: 8),
                    CategoryPill(
                      label: 'Bilyard',
                      icon: '🎱',
                      isSelected: dachaProvider.selectedCategory == 'billiard',
                      onTap: () => dachaProvider.setCategory('billiard'),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 22),

              // Section Title & Counter
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Tavsiya etiladigan dachalar',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                  ),
                  Text(
                    '${dachaProvider.dachas.length} ta dacha',
                    style: const TextStyle(fontSize: 13, color: AppColors.textMuted, fontWeight: FontWeight.w600),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // Dachas List State Handling
              if (dachaProvider.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: CircularProgressIndicator(color: AppColors.primary),
                  ),
                )
              else if (dachaProvider.errorMessage != null)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        const Icon(Icons.cloud_off_rounded, size: 50, color: AppColors.error),
                        const SizedBox(height: 12),
                        const Text(
                          'Serverga ulanishda xatolik',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark),
                        ),
                        const SizedBox(height: 6),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 20),
                          child: Text(
                            dachaProvider.errorMessage!,
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Hozirgi server: ${ApiClient().baseUrl}',
                          style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            OutlinedButton.icon(
                              onPressed: () => ServerConfigDialog.show(context),
                              icon: const Icon(Icons.settings, size: 16),
                              label: const Text('Server sozlamasi'),
                            ),
                            const SizedBox(width: 10),
                            ElevatedButton.icon(
                              onPressed: () => dachaProvider.fetchDachas(),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primary,
                                foregroundColor: Colors.white,
                              ),
                              icon: const Icon(Icons.refresh_rounded, size: 16),
                              label: const Text('Qayta urinish'),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                )
              else if (dachaProvider.dachas.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 50),
                    child: Column(
                      children: [
                        const Text('🏜️', style: TextStyle(fontSize: 48)),
                        const SizedBox(height: 12),
                        const Text(
                          'Hech qanday dacha topilmadi',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Filtrlarni o\'zgartirib yoki tozalab qayta urinib ko\'ring.',
                          style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                        ),
                        if (dachaProvider.hasActiveLocationFilter) ...[
                          const SizedBox(height: 14),
                          OutlinedButton.icon(
                            onPressed: () => dachaProvider.clearFilters(),
                            icon: const Icon(Icons.clear_all_rounded, size: 18),
                            label: const Text('Filtrlarni tozalash'),
                          ),
                        ],
                      ],
                    ),
                  ),
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: dachaProvider.dachas.length,
                  itemBuilder: (context, idx) {
                    final dacha = dachaProvider.dachas[idx];
                    return DachaCard(
                      dacha: dacha,
                      onTap: () {
                        final auth = context.read<AuthProvider>();
                        if (!auth.isAuthenticated) {
                          AuthModal.show(
                            context,
                            notice: 'Dacha haqida batafsil ma\'lumot, narxlar va dacha egasining kontaktlarini ko\'rish uchun iltimos, avval tizimga kiring.',
                          );
                          return;
                        }

                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => DachaDetailScreen(dachaId: dacha.id),
                          ),
                        );
                      },
                    );
                  },
                ),

              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }
}
