import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/constants/app_colors.dart';
import '../providers/dacha_provider.dart';

class LocationFilterModal extends StatefulWidget {
  const LocationFilterModal({super.key});

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const LocationFilterModal(),
    );
  }

  @override
  State<LocationFilterModal> createState() => _LocationFilterModalState();
}

class _LocationFilterModalState extends State<LocationFilterModal> {
  String? _tempRegion;
  String? _tempDistrict;
  String? _tempMahalla;
  List<int> _tempAmenityIds = [];

  // Standard fallback database hierarchy if offline or not yet loaded
  static const Map<String, Map<String, List<String>>> _defaultHierarchy = {
    'Toshkent viloyati': {
      'Bo\'stonliq tumani': ['Yusufxona (Chorvoq)', 'Chimyon (Amirsoy)', 'Sijjak (Bochka)', 'Humson', 'Burchmulla', 'Nanay'],
      'Parkent tumani': ['Kumushkon', 'So\'qoq', 'Zarkent'],
      'Qibray tumani': ['Baytqo\'rg\'on', 'O\'nqo\'rg\'on'],
      'Ohangaron tumani': ['Ertosh', 'Sanam'],
    },
    'Jizzax viloyati': {
      'Zomin tumani': ['O\'riklisoy', 'Duoba', 'Baxmal'],
      'Baxmal tumani': ['Novqa', 'Baxmal markazi'],
    },
    'Toshkent shahri': {
      'Yunusobod tumani': ['Hasanboy'],
      'Mirzo Ulug\'bek tumani': ['Qorasuv'],
    },
    'Samarqand viloyati': {
      'Urgut tumani': ['G\'o\'s', 'Chorchinor'],
      'Samarqand tumani': ['Konigil'],
    },
  };

  @override
  void initState() {
    super.initState();
    final provider = context.read<DachaProvider>();
    _tempRegion = provider.selectedRegion;
    _tempDistrict = provider.selectedDistrict;
    _tempMahalla = provider.selectedMahalla;
    _tempAmenityIds = List.from(provider.selectedAmenityIds);

    Future.microtask(() {
      provider.fetchLocations();
      provider.fetchAmenities();
    });
  }

  Map<String, Map<String, List<String>>> _getMergedHierarchy(Map<String, Map<String, List<String>>> apiHierarchy) {
    final Map<String, Map<String, List<String>>> merged = {};

    _defaultHierarchy.forEach((reg, dists) {
      merged[reg] = Map.from(dists);
    });

    apiHierarchy.forEach((reg, dists) {
      if (!merged.containsKey(reg)) {
        merged[reg] = {};
      }
      dists.forEach((dist, mahallas) {
        if (!merged[reg]!.containsKey(dist)) {
          merged[reg]![dist] = [];
        }
        for (var m in mahallas) {
          if (!merged[reg]![dist]!.contains(m)) {
            merged[reg]![dist]!.add(m);
          }
        }
      });
    });

    return merged;
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DachaProvider>();
    final hierarchy = _getMergedHierarchy(provider.locationsHierarchy);

    final regions = hierarchy.keys.toList();
    final districts = _tempRegion != null && hierarchy.containsKey(_tempRegion)
        ? hierarchy[_tempRegion]!.keys.toList()
        : <String>[];
    final mahallas = _tempRegion != null && _tempDistrict != null && hierarchy.containsKey(_tempRegion) && hierarchy[_tempRegion]!.containsKey(_tempDistrict)
        ? hierarchy[_tempRegion]![_tempDistrict]!
        : <String>[];

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: EdgeInsets.only(
        top: 16,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Drag Handle
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
            ),
          ),
          const SizedBox(height: 16),

          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.location_on_rounded, color: AppColors.primary, size: 24),
                  SizedBox(width: 8),
                  Text(
                    'Hudud bo\'yicha saralash',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                  ),
                ],
              ),
              if (_tempRegion != null || _tempDistrict != null || _tempMahalla != null)
                TextButton(
                  onPressed: () {
                    setState(() {
                      _tempRegion = null;
                      _tempDistrict = null;
                      _tempMahalla = null;
                    });
                  },
                  child: const Text('Tozalash', style: TextStyle(color: AppColors.error, fontSize: 13, fontWeight: FontWeight.w700)),
                ),
            ],
          ),

          const SizedBox(height: 12),

          // Active Path Breadcrumb
          if (_tempRegion != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: AppColors.primaryLight,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.primary.withOpacity(0.2)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.route_rounded, size: 16, color: AppColors.primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      [
                        _tempRegion,
                        if (_tempDistrict != null) _tempDistrict,
                        if (_tempMahalla != null) _tempMahalla,
                      ].join(' ➔ '),
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.primary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),

          ConstrainedBox(
            constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.55),
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Step 1: Viloyat
                  const Text(
                    '1. Viloyatni tanlang:',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.dark),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ChoiceChip(
                        label: const Text('Barcha viloyatlar'),
                        selected: _tempRegion == null,
                        selectedColor: AppColors.primary,
                        labelStyle: TextStyle(
                          color: _tempRegion == null ? Colors.white : AppColors.dark,
                          fontWeight: FontWeight.w600,
                          fontSize: 12.5,
                        ),
                        backgroundColor: AppColors.bgSurface,
                        onSelected: (selected) {
                          if (selected) {
                            setState(() {
                              _tempRegion = null;
                              _tempDistrict = null;
                              _tempMahalla = null;
                            });
                          }
                        },
                      ),
                      ...regions.map((reg) {
                        final isSelected = _tempRegion == reg;
                        return ChoiceChip(
                          label: Text(reg),
                          selected: isSelected,
                          selectedColor: AppColors.primary,
                          labelStyle: TextStyle(
                            color: isSelected ? Colors.white : AppColors.dark,
                            fontWeight: FontWeight.w600,
                            fontSize: 12.5,
                          ),
                          backgroundColor: AppColors.bgSurface,
                          onSelected: (selected) {
                            setState(() {
                              _tempRegion = selected ? reg : null;
                              _tempDistrict = null;
                              _tempMahalla = null;
                            });
                          },
                        );
                      }),
                    ],
                  ),

                  // Step 2: Tuman / Shahar (If Region Selected)
                  if (_tempRegion != null && districts.isNotEmpty) ...[
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        const Text(
                          '2. Tuman / Shahar:',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        const SizedBox(width: 8),
                        Text('($_tempRegion)', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        ChoiceChip(
                          label: const Text('Barcha tumanlar'),
                          selected: _tempDistrict == null,
                          selectedColor: AppColors.primary,
                          labelStyle: TextStyle(
                            color: _tempDistrict == null ? Colors.white : AppColors.dark,
                            fontWeight: FontWeight.w600,
                            fontSize: 12.5,
                          ),
                          backgroundColor: AppColors.bgSurface,
                          onSelected: (selected) {
                            if (selected) {
                              setState(() {
                                _tempDistrict = null;
                                _tempMahalla = null;
                              });
                            }
                          },
                        ),
                        ...districts.map((dist) {
                          final isSelected = _tempDistrict == dist;
                          return ChoiceChip(
                            label: Text(dist),
                            selected: isSelected,
                            selectedColor: AppColors.primary,
                            labelStyle: TextStyle(
                              color: isSelected ? Colors.white : AppColors.dark,
                              fontWeight: FontWeight.w600,
                              fontSize: 12.5,
                            ),
                            backgroundColor: AppColors.bgSurface,
                            onSelected: (selected) {
                              setState(() {
                                _tempDistrict = selected ? dist : null;
                                _tempMahalla = null;
                              });
                            },
                          );
                        }),
                      ],
                    ),
                  ],

                  // Step 3: Mahalla / Qishloq (If District Selected)
                  if (_tempRegion != null && _tempDistrict != null && mahallas.isNotEmpty) ...[
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        const Text(
                          '3. Mahalla / Qishloq:',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        const SizedBox(width: 8),
                        Text('($_tempDistrict)', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        ChoiceChip(
                          label: const Text('Barcha mahallalar'),
                          selected: _tempMahalla == null,
                          selectedColor: AppColors.primary,
                          labelStyle: TextStyle(
                            color: _tempMahalla == null ? Colors.white : AppColors.dark,
                            fontWeight: FontWeight.w600,
                            fontSize: 12.5,
                          ),
                          backgroundColor: AppColors.bgSurface,
                          onSelected: (selected) {
                            if (selected) {
                              setState(() => _tempMahalla = null);
                            }
                          },
                        ),
                        ...mahallas.map((mah) {
                          final cleanMah = mah.replaceAll(RegExp(r'\s*\([^)]*\)'), '').trim();
                          final isSelected = _tempMahalla == cleanMah || _tempMahalla == mah;
                          return ChoiceChip(
                            label: Text(mah),
                            selected: isSelected,
                            selectedColor: AppColors.primary,
                            labelStyle: TextStyle(
                              color: isSelected ? Colors.white : AppColors.dark,
                              fontWeight: FontWeight.w600,
                              fontSize: 12.5,
                            ),
                            backgroundColor: AppColors.bgSurface,
                            onSelected: (selected) {
                              setState(() {
                                _tempMahalla = selected ? cleanMah : null;
                              });
                            },
                          );
                        }),
                      ],
                    ),
                  ],
                  // Step 4: Qulayliklar (Amenities Multi-Select)
                  if (provider.amenities.isNotEmpty) ...[
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          '4. Qulayliklar (Bir nechtasini tanlang):',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.dark),
                        ),
                        if (_tempAmenityIds.isNotEmpty)
                          Text(
                            '${_tempAmenityIds.length} ta tanlandi',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.primary),
                          ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: provider.amenities.map((amenity) {
                        final isSelected = _tempAmenityIds.contains(amenity.id);
                        return FilterChip(
                          avatar: Text(amenity.icon ?? '✨', style: const TextStyle(fontSize: 14)),
                          label: Text(amenity.name),
                          selected: isSelected,
                          selectedColor: AppColors.primaryLight,
                          checkmarkColor: AppColors.primary,
                          labelStyle: TextStyle(
                            color: isSelected ? AppColors.primary : AppColors.dark,
                            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                            fontSize: 12.5,
                          ),
                          backgroundColor: AppColors.bgSurface,
                          side: BorderSide(
                            color: isSelected ? AppColors.primary : AppColors.border,
                            width: isSelected ? 1.5 : 1,
                          ),
                          onSelected: (selected) {
                            setState(() {
                              if (selected) {
                                _tempAmenityIds.add(amenity.id);
                              } else {
                                _tempAmenityIds.remove(amenity.id);
                              }
                            });
                          },
                        );
                      }).toList(),
                    ),
                  ],
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Apply Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                provider.setFilter(
                  region: _tempRegion,
                  district: _tempDistrict,
                  mahalla: _tempMahalla,
                  amenityIds: _tempAmenityIds,
                );
                Navigator.pop(context);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              child: Text(
                _tempAmenityIds.isNotEmpty
                    ? 'Filtrni qo\'llash (${_tempAmenityIds.length} ta qulaylik) ✨'
                    : 'Filtrni qo\'llash ✨',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
