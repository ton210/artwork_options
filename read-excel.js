const XLSX = require('xlsx');
const fs = require('fs');

const filePath = '/Users/tomernahumi/Green Lunar 2022 Dropbox/OD Files/can you make one single list combined.xlsx';

try {
  // Read the Excel file
  const workbook = XLSX.readFile(filePath);

  console.log('Sheet names:', workbook.SheetNames);

  const allData = [];

  // Process each sheet
  workbook.SheetNames.forEach(sheetName => {
    console.log(`\n=== Processing sheet: ${sheetName} ===`);
    const worksheet = workbook.Sheets[sheetName];
    const data = XLSX.utils.sheet_to_json(worksheet);

    console.log(`Rows: ${data.length}`);
    if (data.length > 0) {
      console.log('Columns:', Object.keys(data[0]));
      console.log('First row sample:', data[0]);
    }

    // Add sheet name to each record
    data.forEach(record => {
      record._sheet = sheetName;
      allData.push(record);
    });
  });

  // Save combined data
  const outputFile = './arizona-dispensaries.json';
  fs.writeFileSync(outputFile, JSON.stringify(allData, null, 2));

  console.log(`\n✓ Combined data saved to ${outputFile}`);
  console.log(`✓ Total records: ${allData.length}`);

} catch (error) {
  console.error('Error:', error.message);
  console.error(error.stack);
}
