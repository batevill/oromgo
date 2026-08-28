import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/dacha_model.dart';
import '../../providers/dacha_provider.dart';
import '../../services/owner_service.dart';
import '../../widgets/custom_button.dart';

class CreateEditDachaScreen extends StatefulWidget {
  final DachaModel? dacha;

  const CreateEditDachaScreen({super.key, this.dacha});

  @override
  State<CreateEditDachaScreen> createState() => _CreateEditDachaScreenState();
}

class _CreateEditDachaScreenState extends State<CreateEditDachaScreen> {
  final _formKey = GlobalKey<FormState>();
  final OwnerService _ownerService = OwnerService();

  late TextEditingController _nameController;
  late TextEditingController _descriptionController;
  late TextEditingController _mahallaController;
  late TextEditingController _addressController;
  late TextEditingController _weekdayPriceController;
  late TextEditingController _weekendPriceController;
  late TextEditingController _capacityController;
  late TextEditingController _roomsCountController;

  String _selectedRegion = 'Toshkent viloyati';
  String _selectedDistrict = 'Bo\'stonliq tumani';
  String _currency = 'USD';
  final Set<int> _selectedAmenityIds = {};
  bool _isSaving = false;

  final List<String> _regions = [
    'Toshkent viloyati',
    'Toshkent shahri',
    'Jizzax viloyati',
    'Samarqand viloyati',
    'Namangan viloyati',
    'Farg\'ona viloyati',
    'Andijon viloyati',
  ];

  final List<String> _districts = [
    'Bo\'stonliq tumani',
    'Chorvoq',
    'Yusufxona',
    'Burchmulla',
    'Chimyon',
    'Amirsoy',
    'Zomin tumani',
    'Oqtosh',
    'Parkent tumani',
    'Qibray tumani',
  ];

  @override
  void initState() {
    super.initState();
    final d = widget.dacha;

    _nameController = TextEditingController(text: d?.name ?? '');
    _descriptionController = TextEditingController(text: d?.description ?? '');
    _mahallaController = TextEditingController(text: d?.mahalla ?? '');
    _addressController = TextEditingController(text: d?.address ?? '');
    _weekdayPriceController = TextEditingController(
      text: d != null ? (d.weekdayPrice > 0 ? d.weekdayPrice.toInt().toString() : '') : '150',
    );
    _weekendPriceController = TextEditingController(
      text: d != null ? (d.weekendPrice > 0 ? d.weekendPrice.toInt().toString() : '') : '200',
    );
    _capacityController = TextEditingController(text: d != null ? d.capacity.toString() : '10');
    _roomsCountController = TextEditingController(text: d != null ? d.roomsCount.toString() : '4');

    if (d != null) {
      if (_regions.contains(d.region)) {
        _selectedRegion = d.region;
      }
      if (_districts.contains(d.district)) {
        _selectedDistrict = d.district;
      }
      _currency = d.currency;
      _selectedAmenityIds.addAll(d.amenities.map((a) => a.id));
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _mahallaController.dispose();
    _addressController.dispose();
    _weekdayPriceController.dispose();
    _weekendPriceController.dispose();
    _capacityController.dispose();
    _roomsCountController.dispose();
    super.dispose();
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    try {
      final body = {
        'name': _nameController.text.trim(),
        'description': _descriptionController.text.trim(),
        'region': _selectedRegion,
        'district': _selectedDistrict,
        'mahalla': _mahallaController.text.trim().isNotEmpty ? _mahallaController.text.trim() : null,
        'address': _addressController.text.trim().isNotEmpty ? _addressController.text.trim() : null,
        'weekday_price': double.tryParse(_weekdayPriceController.text.trim()) ?? 0,
        'weekend_price': double.tryParse(_weekendPriceController.text.trim()) ?? 0,
        'currency': _currency,
        'capacity': int.tryParse(_capacityController.text.trim()) ?? 1,
        'rooms_count': int.tryParse(_roomsCountController.text.trim()) ?? 1,
        'amenities': _selectedAmenityIds.toList(),
      };

      if (widget.dacha != null) {
        await _ownerService.updateDacha(widget.dacha!.id, body);
      } else {
        await _ownerService.createDacha(body);
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              widget.dacha != null
                  ? '🎉 Dacha ma\'lumotlari muvaffaqiyatli yangilandi!'
                  : '🎉 Yangi dacha e\'loningiz muvaffaqiyatli joylandi!',
            ),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik yuz berdi: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.dacha != null;
    final amenities = context.watch<DachaProvider>().amenities;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          isEdit ? 'Dachani tahrirlash ✏️' : 'Yangi e\'lon joylash 🏡',
          style: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildSectionTitle('Asosiy ma\'lumotlar'),
              _buildTextField(
                controller: _nameController,
                label: 'Dacha nomi (Sarlavha) *',
                hint: 'Masalan: Chorvoq Panorama Lux Dacha',
                validator: (v) => v == null || v.trim().isEmpty ? 'Sarlavha kiritilishi shart' : null,
              ),
              const SizedBox(height: 14),
              _buildTextField(
                controller: _descriptionController,
                label: 'Tavsif',
                hint: 'Dacha sharoitlari va afzalliklari haqida batafsil...',
                maxLines: 3,
              ),

              const SizedBox(height: 24),
              _buildSectionTitle('Joylashuv'),
              Row(
                children: [
                  Expanded(
                    child: _buildDropdown(
                      label: 'Viloyat *',
                      value: _selectedRegion,
                      items: _regions,
                      onChanged: (v) {
                        if (v != null) setState(() => _selectedRegion = v);
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildDropdown(
                      label: 'Tuman / Hudud *',
                      value: _selectedDistrict,
                      items: _districts,
                      onChanged: (v) {
                        if (v != null) setState(() => _selectedDistrict = v);
                      },
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _buildTextField(
                      controller: _mahallaController,
                      label: 'Mahalla / Qishloq',
                      hint: 'Yusufxona',
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildTextField(
                      controller: _addressController,
                      label: 'Aniq manzil',
                      hint: 'Soy bo\'yi 12-uy',
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),
              _buildSectionTitle('Narxlar va Sig\'im'),
              Row(
                children: [
                  Expanded(
                    child: _buildTextField(
                      controller: _weekdayPriceController,
                      label: 'Ish kunlari narxi *',
                      hint: '150',
                      keyboardType: TextInputType.number,
                      validator: (v) => v == null || v.trim().isEmpty ? 'Narx kiritilishi shart' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildTextField(
                      controller: _weekendPriceController,
                      label: 'Dam olish narxi',
                      hint: '200',
                      keyboardType: TextInputType.number,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _buildDropdown(
                      label: 'Valyuta *',
                      value: _currency,
                      items: const ['USD', 'UZS'],
                      onChanged: (v) {
                        if (v != null) setState(() => _currency = v);
                      },
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildTextField(
                      controller: _capacityController,
                      label: 'Sig\'im (kishi) *',
                      hint: '10',
                      keyboardType: TextInputType.number,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildTextField(
                      controller: _roomsCountController,
                      label: 'Xonalar soni *',
                      hint: '4',
                      keyboardType: TextInputType.number,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),
              _buildSectionTitle('Qulayliklar (Mavjudlarini tanlang)'),
              if (amenities.isNotEmpty)
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: amenities.map((a) {
                    final isSelected = _selectedAmenityIds.contains(a.id);
                    return FilterChip(
                      label: Text('${a.icon ?? "✨"} ${a.name}'),
                      selected: isSelected,
                      selectedColor: AppColors.primaryLight,
                      checkmarkColor: AppColors.primary,
                      labelStyle: TextStyle(
                        fontSize: 12.5,
                        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                        color: isSelected ? AppColors.primary : AppColors.dark,
                      ),
                      onSelected: (selected) {
                        setState(() {
                          if (selected) {
                            _selectedAmenityIds.add(a.id);
                          } else {
                            _selectedAmenityIds.remove(a.id);
                          }
                        });
                      },
                    );
                  }).toList(),
                )
              else
                const Text('Qulayliklar yuklanmoqda...', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),

              const SizedBox(height: 32),
              CustomButton(
                text: isEdit ? '💾 Saqlash va yangilash' : '✨ E\'lonni joylash',
                isLoading: _isSaving,
                onPressed: _submitForm,
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        title,
        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.dark),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    String? hint,
    int maxLines = 1,
    TextInputType keyboardType = TextInputType.text,
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.dark)),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          maxLines: maxLines,
          keyboardType: keyboardType,
          validator: validator,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(fontSize: 13.5, color: AppColors.textMuted),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
          ),
        ),
      ],
    );
  }

  Widget _buildDropdown({
    required String label,
    required String value,
    required List<String> items,
    required void Function(String?) onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.dark)),
        const SizedBox(height: 6),
        DropdownButtonFormField<String>(
          value: items.contains(value) ? value : items.first,
          items: items.map((i) => DropdownMenuItem(value: i, child: Text(i, style: const TextStyle(fontSize: 13.5)))).toList(),
          onChanged: onChanged,
          decoration: InputDecoration(
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
          ),
        ),
      ],
    );
  }
}
