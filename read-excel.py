import pandas as pd
import json

# Read the Excel file
file_path = "/Users/tomernahumi/Green Lunar 2022 Dropbox/OD Files/can you make one single list combined.xlsx"

try:
    # Read all sheets
    excel_file = pd.ExcelFile(file_path)
    print(f"Sheet names: {excel_file.sheet_names}")

    all_data = []

    for sheet_name in excel_file.sheet_names:
        df = pd.read_excel(file_path, sheet_name=sheet_name)
        print(f"\n=== Sheet: {sheet_name} ===")
        print(f"Columns: {df.columns.tolist()}")
        print(f"Rows: {len(df)}")
        print(f"\nFirst few rows:")
        print(df.head())

        # Convert to dict for easier processing
        records = df.to_dict('records')
        for record in records:
            record['_sheet'] = sheet_name
            all_data.append(record)

    # Save combined data to JSON
    output_file = "/Users/tomernahumi/Documents/Plugins/DispensariesMunchmakers/arizona-dispensaries.json"
    with open(output_file, 'w') as f:
        json.dump(all_data, f, indent=2, default=str)

    print(f"\n✓ Combined data saved to {output_file}")
    print(f"✓ Total records: {len(all_data)}")

except Exception as e:
    print(f"Error: {e}")
    import traceback
    traceback.print_exc()
