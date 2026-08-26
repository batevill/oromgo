import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/formatters.dart';
import '../../models/dacha_model.dart';
import '../../providers/dacha_provider.dart';
import '../../providers/favorite_provider.dart';
import '../../widgets/booking_bottom_bar.dart';
import '../booking/booking_sheet.dart';

class DachaDetailScreen extends StatefulWidget {
  final int dachaId;

  const DachaDetailScreen({super.key, required this.dachaId});

  @override
  State<DachaDetailScreen> createState() => _DachaDetailScreenState();
}

class _DachaDetailScreenState extends State<DachaDetailScreen> {
  int _currentImageIndex = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      context.read<DachaProvider>().fetchDachaDetail(widget.dachaId);
    });
  }

  @override
  Widget build(BuildContext context) {
    final dachaProvider = context.watch<DachaProvider>();
    final dacha = dachaProvider.selectedDacha;
    final favProvider = context.watch<FavoriteProvider>();

    if (dachaProvider.isLoading || dacha == null) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    final isFav = favProvider.isFavorite(dacha.id);
    final images = dacha.media.isNotEmpty ? dacha.media.map((m) => m.url).toList() : [dacha.firstImageUrl];

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // Collapsible Image Header App Bar
          SliverAppBar(
            expandedHeight: 300,
            pinned: true,
            backgroundColor: Colors.white,
            leading: Padding(
              padding: const EdgeInsets.all(8.0),
              child: CircleAvatar(
                backgroundColor: Colors.white.withOpacity(0.9),
                child: IconButton(
                  icon: const Icon(Icons.arrow_back_ios_new, size: 18, color: AppColors.dark),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
            ),
            actions: [
              Padding(
                padding: const EdgeInsets.all(8.0),
                child: CircleAvatar(
                  backgroundColor: Colors.white.withOpacity(0.9),
                  child: IconButton(
                    icon: Icon(
                      isFav ? Icons.favorite : Icons.favorite_border,
                      color: isFav ? const Color(0xFFE11D48) : AppColors.dark,
                      size: 20,
                    ),
                    onPressed: () => favProvider.toggleFavorite(dacha),
                  ),
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                children: [
                  PageView.builder(
                    itemCount: images.length,
                    onPageChanged: (idx) => setState(() => _currentImageIndex = idx),
                    itemBuilder: (context, idx) {
                      return CachedNetworkImage(
                        imageUrl: images[idx],
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => Container(color: AppColors.borderLight),
                      );
                    },
                  ),
                  if (images.length > 1)
                    Positioned(
                      bottom: 16,
                      right: 16,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.65),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Text(
                          '${_currentImageIndex + 1} / ${images.length}',
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),

          // Content Details
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title & Rating
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          dacha.name,
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.dark),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.accentLight,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.star_rounded, color: AppColors.accent, size: 18),
                            const SizedBox(width: 2),
                            Text(
                              dacha.avgRating.toStringAsFixed(1),
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.dark),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, color: AppColors.textMuted, size: 16),
                      const SizedBox(width: 4),
                      Text(
                        '${dacha.district}, ${dacha.region} (${dacha.address ?? "So\'lim tog\' yonbag\'ri"})',
                        style: const TextStyle(fontSize: 13.5, color: AppColors.textMuted, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),
                  const Divider(color: AppColors.border),
                  const SizedBox(height: 16),

                  // Specs Row (Capacity, Rooms)
                  Row(
                    children: [
                      _buildSpecItem(Icons.people_alt_outlined, '${dacha.capacity} kishilik', 'Maksimal sig\'im'),
                      const SizedBox(width: 20),
                      _buildSpecItem(Icons.bed_outlined, '${dacha.roomsCount} xona', 'Yotoqxona va zallar'),
                    ],
                  ),

                  const SizedBox(height: 20),
                  const Divider(color: AppColors.border),
                  const SizedBox(height: 16),

                  // Description
                  const Text('Tavsif', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.dark)),
                  const SizedBox(height: 8),
                  Text(
                    dacha.description ?? 'Ushbu dacha tog\' bag\'rida, toza havoda va barcha qulayliklarga ega holda joylashgan.',
                    style: const TextStyle(fontSize: 14, color: AppColors.darkLight, height: 1.5),
                  ),

                  const SizedBox(height: 24),

                  // Amenities Grid
                  if (dacha.amenities.isNotEmpty) ...[
                    const Text('Mavjud qulayliklar', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.dark)),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: dacha.amenities.map((a) {
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: AppColors.bgPage,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(a.icon ?? '✨', style: const TextStyle(fontSize: 16)),
                              const SizedBox(width: 6),
                              Text(a.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.dark)),
                            ],
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // Owner Info Card
                  if (dacha.owner != null) ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.bgPage,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: AppColors.primaryLight,
                            child: Text(dacha.owner!.name[0], style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.primary)),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(dacha.owner!.name, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.dark)),
                                const Text('Dacha Egasi (Tasdiqlangan)', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(color: AppColors.primaryLight, borderRadius: BorderRadius.circular(12)),
                            child: const Icon(Icons.verified, color: AppColors.primary, size: 20),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 30),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: BookingBottomBar(
        dacha: dacha,
        onBookTap: () {
          BookingSheet.show(context, dacha);
        },
      ),
    );
  }

  Widget _buildSpecItem(IconData icon, String title, String subtitle) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: AppColors.primaryLight, borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: AppColors.primary, size: 22),
        ),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.dark)),
            Text(subtitle, style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
          ],
        ),
      ],
    );
  }
}
