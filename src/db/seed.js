const db = require('../config/database');
const fs = require('fs');
const path = require('path');

// County data for each state
const stateCounties = {
  'Alabama': ['Jefferson', 'Mobile', 'Madison', 'Montgomery', 'Tuscaloosa', 'Shelby', 'Baldwin'],
  'Alaska': ['Anchorage', 'Fairbanks North Star', 'Matanuska-Susitna', 'Kenai Peninsula', 'Juneau'],
  'Arkansas': ['Pulaski', 'Benton', 'Washington', 'Sebastian', 'Faulkner', 'Saline', 'Craighead'],
  'Arizona': ['Maricopa', 'Pima', 'Pinal', 'Yavapai', 'Mohave', 'Coconino', 'Yuma'],
  'California': ['Los Angeles', 'San Diego', 'Orange', 'Riverside', 'San Bernardino', 'Santa Clara', 'Alameda', 'Sacramento', 'Contra Costa', 'Fresno', 'Kern', 'San Francisco', 'Ventura', 'San Mateo', 'San Joaquin', 'Stanislaus', 'Sonoma', 'Tulare', 'Solano', 'Santa Barbara', 'Monterey', 'Placer', 'San Luis Obispo', 'Santa Cruz', 'Merced', 'Butte', 'Yolo', 'El Dorado', 'Shasta', 'Imperial', 'Humboldt', 'Mendocino', 'Nevada', 'Marin'],
  'Colorado': ['Denver', 'El Paso', 'Arapahoe', 'Jefferson', 'Adams', 'Boulder', 'Larimer', 'Weld', 'Pueblo', 'Mesa'],
  'Connecticut': ['Fairfield', 'Hartford', 'New Haven', 'New London', 'Litchfield', 'Middlesex', 'Tolland', 'Windham'],
  'Delaware': ['New Castle', 'Sussex', 'Kent'],
  'Florida': ['Miami-Dade', 'Broward', 'Palm Beach', 'Hillsborough', 'Orange', 'Pinellas', 'Duval', 'Lee', 'Polk', 'Brevard', 'Volusia', 'Pasco', 'Seminole', 'Sarasota', 'Manatee', 'Collier'],
  'Hawaii': ['Honolulu', 'Hawaii', 'Maui', 'Kauai'],
  'Illinois': ['Cook', 'DuPage', 'Lake', 'Will', 'Kane', 'McHenry', 'Winnebago', 'Madison', 'St. Clair', 'Sangamon'],
  'Kentucky': ['Jefferson', 'Fayette', 'Warren', 'Hardin', 'Kenton', 'Boone', 'Daviess', 'Campbell'],
  'Louisiana': ['East Baton Rouge', 'Jefferson', 'Orleans', 'St. Tammany', 'Lafayette', 'Caddo', 'Calcasieu', 'Livingston'],
  'Maine': ['Cumberland', 'York', 'Penobscot', 'Kennebec', 'Androscoggin', 'Hancock', 'Oxford'],
  'Maryland': ['Montgomery', 'Prince George\'s', 'Baltimore', 'Anne Arundel', 'Howard', 'Harford', 'Frederick', 'Carroll', 'Charles'],
  'Massachusetts': ['Middlesex', 'Worcester', 'Essex', 'Suffolk', 'Norfolk', 'Bristol', 'Plymouth', 'Hampden', 'Barnstable', 'Hampshire', 'Berkshire', 'Franklin'],
  'Michigan': ['Wayne', 'Oakland', 'Macomb', 'Kent', 'Genesee', 'Washtenaw', 'Ottawa', 'Ingham', 'Kalamazoo', 'Livingston'],
  'Minnesota': ['Hennepin', 'Ramsey', 'Dakota', 'Anoka', 'Washington', 'St. Louis', 'Olmsted', 'Scott', 'Wright'],
  'Mississippi': ['Harrison', 'Hinds', 'DeSoto', 'Rankin', 'Madison', 'Jackson', 'Lee', 'Forrest'],
  'Missouri': ['St. Louis', 'Jackson', 'St. Charles', 'Jefferson', 'Greene', 'Clay', 'Boone', 'Cass', 'Franklin'],
  'Montana': ['Yellowstone', 'Missoula', 'Gallatin', 'Flathead', 'Cascade', 'Lewis and Clark', 'Silver Bow'],
  'Nevada': ['Clark', 'Washoe', 'Carson City', 'Lyon', 'Elko', 'Douglas'],
  'New Hampshire': ['Hillsborough', 'Rockingham', 'Merrimack', 'Strafford', 'Grafton', 'Belknap', 'Carroll'],
  'New Jersey': ['Bergen', 'Middlesex', 'Essex', 'Hudson', 'Monmouth', 'Ocean', 'Union', 'Camden', 'Passaic', 'Morris', 'Burlington', 'Mercer', 'Somerset'],
  'New Mexico': ['Bernalillo', 'Doña Ana', 'Santa Fe', 'Sandoval', 'San Juan', 'Valencia', 'Lea', 'Otero'],
  'New York': ['Kings', 'Queens', 'New York', 'Suffolk', 'Bronx', 'Nassau', 'Westchester', 'Erie', 'Monroe', 'Richmond', 'Onondaga', 'Orange', 'Rockland', 'Albany', 'Dutchess', 'Saratoga', 'Oneida', 'Niagara'],
  'North Dakota': ['Cass', 'Burleigh', 'Grand Forks', 'Ward', 'Williams', 'Stark', 'Morton'],
  'Ohio': ['Cuyahoga', 'Franklin', 'Hamilton', 'Summit', 'Montgomery', 'Lucas', 'Stark', 'Butler', 'Lorain', 'Mahoning', 'Warren', 'Lake'],
  'Oklahoma': ['Oklahoma', 'Tulsa', 'Cleveland', 'Canadian', 'Comanche', 'Rogers', 'Wagoner', 'Creek'],
  'Oregon': ['Multnomah', 'Washington', 'Clackamas', 'Lane', 'Marion', 'Jackson', 'Deschutes', 'Linn', 'Douglas'],
  'Pennsylvania': ['Philadelphia', 'Allegheny', 'Montgomery', 'Bucks', 'Delaware', 'Chester', 'Lancaster', 'York', 'Dauphin', 'Westmoreland', 'Berks', 'Luzerne'],
  'Rhode Island': ['Providence', 'Kent', 'Washington', 'Newport', 'Bristol'],
  'South Dakota': ['Minnehaha', 'Pennington', 'Lincoln', 'Brown', 'Brookings', 'Codington', 'Davison'],
  'Utah': ['Salt Lake', 'Utah', 'Davis', 'Weber', 'Washington', 'Cache', 'Summit'],
  'Vermont': ['Chittenden', 'Rutland', 'Washington', 'Windsor', 'Bennington', 'Franklin', 'Addison', 'Orange'],
  'Virginia': ['Fairfax', 'Prince William', 'Virginia Beach', 'Loudoun', 'Chesterfield', 'Henrico', 'Norfolk', 'Chesapeake', 'Richmond', 'Newport News', 'Alexandria', 'Hampton'],
  'Washington': ['King', 'Pierce', 'Snohomish', 'Spokane', 'Clark', 'Thurston', 'Kitsap', 'Yakima', 'Whatcom', 'Benton'],
  'Washington D.C.': ['Washington'],
  'West Virginia': ['Kanawha', 'Berkeley', 'Monongalia', 'Cabell', 'Wood', 'Raleigh', 'Harrison']
};

async function seedDatabase() {
  console.log('Starting database seeding...');

  try {
    // Load legal states from JSON
    const legalStatesPath = path.join(__dirname, '../../data/legal-states.json');
    const legalStates = JSON.parse(fs.readFileSync(legalStatesPath, 'utf8'));

    console.log(`Loading ${legalStates.length} legal states...`);

    // Insert states
    for (const state of legalStates) {
      const result = await db.query(
        `INSERT INTO states (name, slug, abbreviation)
         VALUES ($1, $2, $3)
         ON CONFLICT (slug) DO UPDATE
         SET name = EXCLUDED.name, abbreviation = EXCLUDED.abbreviation
         RETURNING id`,
        [state.name, state.slug, state.abbreviation]
      );

      const stateId = result.rows[0].id;
      console.log(`✓ Inserted state: ${state.name} (ID: ${stateId})`);

      // Insert counties for this state
      const counties = stateCounties[state.name] || [];

      for (const countyName of counties) {
        const countySlug = countyName.toLowerCase()
          .replace(/\s+/g, '-')
          .replace(/[^a-z0-9-]/g, '');

        await db.query(
          `INSERT INTO counties (state_id, name, slug)
           VALUES ($1, $2, $3)
           ON CONFLICT (state_id, slug) DO UPDATE
           SET name = EXCLUDED.name`,
          [stateId, countyName, countySlug]
        );
      }

      console.log(`  ✓ Inserted ${counties.length} counties for ${state.name}`);
    }

    console.log('✓ Database seeding completed successfully!');

    // Display summary
    const stateCount = await db.query('SELECT COUNT(*) FROM states');
    const countyCount = await db.query('SELECT COUNT(*) FROM counties');

    console.log('\nDatabase Summary:');
    console.log(`  States: ${stateCount.rows[0].count}`);
    console.log(`  Counties: ${countyCount.rows[0].count}`);

  } catch (error) {
    console.error('Seeding failed:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

// Run seeding if called directly
if (require.main === module) {
  seedDatabase()
    .then(() => {
      console.log('Seeding script finished');
      process.exit(0);
    })
    .catch((err) => {
      console.error('Seeding script failed:', err);
      process.exit(1);
    });
}

module.exports = { seedDatabase };
