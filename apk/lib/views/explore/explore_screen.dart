import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/dacha_provider.dart';
import '../../widgets/category_pill.dart';
import '../../widgets/dacha_card.dart';
import '../../widgets/search_bar_widget.dart';
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
    });
  }

  void _showFilterModal() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) {
        final dachaProvider = context.read<DachaProvider>();
        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Hududni tanlang', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark)),
              const SizedBox(height: 16),
              ListTile(
                title: const Text('Barcha hududlar'),
                trailing: dachaProvider.selectedRegion == null ? const Icon(Icons.check, color: AppColors.primary) : null,
                onTap: () {
                  dachaProvider.setRegion(null);
                  Navigator.pop(context);
                },
              ),
              ListTile(
                title: const Text('Toshkent viloyati (Chorvoq/Chimyon)'),
                trailing: dachaProvider.selectedRegion == 'Toshkent viloyati' ? const Icon(Icons.check, color: AppColors.primary) : null,
                onTap: () {
                  dachaProvider.setRegion('Toshkent viloyati');
                  Navigator.pop(context);
                },
              ),
              ListTile(
                title: const Text('Jizzax viloyati (Zomin)'),
                trailing: dachaProvider.selectedRegion == 'Jizzax viloyati' ? const Icon(Icons.check, color: AppColors.primary) : null,
                onTap: () {
                  dachaProvider.setRegion('Jizzax viloyati');
                  Navigator.pop(context);
                },
              ),
            ],
          ),
        );
      },
    );
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
                  // search query
                },
              ),
              const SizedBox(height: 16),

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

              // Dachas List
              if (dachaProvider.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: CircularProgressIndicator(color: AppColors.primary),
                  ),
                )
              else if (dachaProvider.dachas.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 60),
                    child: Column(
                      children: [
                        const Text('🏜️', style: TextStyle(fontSize: 48)),
                        const SizedBox(height: 12),
                        const Text(
                          'Hech qanday dacha topilmadi',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        const SizedBox(height: 4),
                        const Text('Filtrlarni o\'zgartirib qayta urinib ko\'ring.', style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
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
