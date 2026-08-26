import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/constants/app_colors.dart';
import '../core/utils/formatters.dart';
import '../models/dacha_model.dart';
import '../providers/favorite_provider.dart';

class DachaCard extends StatelessWidget {
  final DachaModel dacha;
  final VoidCallback onTap;

  const DachaCard({
    super.key,
    required this.dacha,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final favProvider = context.watch<FavoriteProvider>();
    final isFav = favProvider.isFavorite(dacha.id);

    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image with Badges & Favorite Button
            Stack(
              children: [
                Hero(
                  tag: 'dacha_img_${dacha.id}',
                  child: CachedNetworkImage(
                    imageUrl: dacha.firstImageUrl,
                    height: 210,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      height: 210,
                      color: AppColors.borderLight,
                      child: const Center(
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                      ),
                    ),
                    errorWidget: (context, url, error) => Container(
                      height: 210,
                      color: AppColors.borderLight,
                      child: const Icon(Icons.broken_image, size: 40, color: AppColors.textMuted),
                    ),
                  ),
                ),

                // Location Badge
                Positioned(
                  top: 14,
                  left: 14,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.black.withOpacity(0.65),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.location_on, color: Colors.white, size: 13),
                        const SizedBox(width: 4),
                        Text(
                          '${dacha.district}, ${dacha.region.split(" ")[0]}',
                          style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                ),

                // Favorite Heart Button
                Positioned(
                  top: 14,
                  right: 14,
                  child: GestureDetector(
                    onTap: () {
                      favProvider.toggleFavorite(dacha);
                    },
                    child: Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 6),
                        ],
                      ),
                      child: Icon(
                        isFav ? Icons.favorite : Icons.favorite_border,
                        color: isFav ? const Color(0xFFE11D48) : AppColors.dark,
                        size: 20,
                      ),
                    ),
                  ),
                ),
              ],
            ),

            // Card Body Info
            Padding(
              padding: const EdgeInsets.all(16),
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
                          style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: AppColors.dark),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Row(
                        children: [
                          const Icon(Icons.star_rounded, color: AppColors.accent, size: 18),
                          const SizedBox(width: 2),
                          Text(
                            dacha.avgRating.toStringAsFixed(1),
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppColors.dark),
                          ),
                        ],
                      ),
                    ],
                  ),

                  const SizedBox(height: 6),

                  // Specs (Capacity, Rooms)
                  Text(
                    '👥 ${dacha.capacity} kishi  •  🛏️ ${dacha.roomsCount} xona',
                    style: const TextStyle(fontSize: 13, color: AppColors.textMuted, fontWeight: FontWeight.w500),
                  ),

                  const SizedBox(height: 12),
                  const Divider(height: 1, color: AppColors.border),
                  const SizedBox(height: 12),

                  // Pricing row
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Ish kunlari:', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                          Text(
                            Formatters.formatCurrency(dacha.weekdayPrice, currency: dacha.currency),
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.primary),
                          ),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          const Text('Dam olish kuni:', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                          Text(
                            Formatters.formatCurrency(dacha.weekendPrice, currency: dacha.currency),
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.accent),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
