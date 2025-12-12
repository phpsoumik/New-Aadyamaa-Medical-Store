<?php

namespace App\Exports;

//use App\Models\Products;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
//use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class StocksExport implements 
//FromCollection, 
ShouldAutoSize,
WithHeadings, 
WithEvents
//WithMapping
{
	use Exportable;

	public function headings(): array
	{
		$storeTaxes = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('id',Auth::user()->branch_id)
		->get();
		

		return [
			'Product Name (*) [Text]',
			'Generic Name (*) [Text]',
			'Barcode [Text]',
			'Product Type (*) [Text]',
			'Brand Name (*) [Text]',
			'Brand Sector (*) [Text]',
			'Category (*) [Text]',
			'Side Effects [Text]',
			'Strip Size (*) [Number]',
			'Pack Size (*) [Number]',
			'Unit Qty (*) [Number]',
			'Expiry Date (*) [Date DD-MM-YYYY]',
			'Pack Purchase Price (*) [Number]',
			'Maximum Retail Price (MRP) (*) [Number]',
			'Batch No (*) [Text]',
			($storeTaxes[0]->show_1 == 'true' ? $storeTaxes[0]->tax_name_1.' % ('.$storeTaxes[0]->required_optional_1.')'.' [Number]' : 'Leave Empty'),
			($storeTaxes[0]->show_2 == 'true' ? $storeTaxes[0]->tax_name_2.' % ('.$storeTaxes[0]->required_optional_2.')'.' [Number]' : 'Leave Empty'),
			($storeTaxes[0]->show_3 == 'true' ? $storeTaxes[0]->tax_name_3.' % ('.$storeTaxes[0]->required_optional_3.')'.' [Number]' : 'Leave Empty'),
			'Discount Percentage [Number]',
			'Description [Text]',
			'Minimum Stock [Number]',
			'Store Locations [Text]',
		];
	}

	// public function map($product): array
    // {
	// 	$storeTaxes = Branch::with([
	// 		'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
	// 		'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
	// 		'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
	// 	])
	// 	->where('id',Auth::user()->branch_id)
	// 	->get();

	// 	return
	// 	[
	// 		$product->product_name,
	// 		$product->generic,
	// 		$product->barcode,
	// 		$product->productType->name,
	// 		$product->brandName->name,
	// 		$product->brandSector->name,
	// 		$product->categoryName->name,
	// 		$product->side_effects,
	// 		$product->strip_size,
	// 		$product->pack_size,
	// 		0,
	// 		'',
	// 		0,
	// 		0,
	// 		'',
	// 		($storeTaxes[0]->show_1 == 'true' ? $storeTaxes[0]->tax_value_1 : 0),
	// 		($storeTaxes[0]->show_2 == 'true' ? $storeTaxes[0]->tax_value_2 : 0),
	// 		($storeTaxes[0]->show_3 == 'true' ? $storeTaxes[0]->tax_value_3 : 0),
	// 		0,
	// 		'',
	// 		'',
	// 		''
	// 	];
    // }

	// public function collection()
	// {
	// 	return Products::with([
	// 		'brandName:id,option_name as name',
	// 		'brandSector:id,option_name as name',
	// 		'productType:id,option_name as name',
	// 		'categoryName:id,option_name as name',
	// 	])->get();
	// }

	public function registerEvents(): array
	{
		$styleArray = [
			'font' => [
				'bold' => true,
				'size' => 18
			]
		];

		return [
			AfterSheet::class => function (AfterSheet $event) use ($styleArray) {
				$event->sheet->getStyle('A1:G1')->applyFromArray($styleArray);
			}
		];
	}

	public function startCell(): string
	{
		return 'A1';
	}
}
