export interface Brand {
  id: string;
  name: string;
  description: string;
  fullDescription: string;
  website: string;
  logo?: string;
  stats?: string;
  established?: string;
}

export const brands: Brand[] = [
  {
    id: 'munchmakers',
    name: 'MunchMakers',
    description: 'The premier custom smoke accessories provider, trusted by thousands of cannabis businesses worldwide.',
    fullDescription: `MunchMakers is the premier custom smoke accessories provider, trusted by thousands of cannabis businesses worldwide—especially in North America. With over 150 customizable products, each item is tailored uniquely to amplify every client's brand identity.

From custom grinders and rolling trays to branded packaging and promotional items, MunchMakers helps cannabis businesses stand out in a competitive market with high-quality, personalized accessories.`,
    website: 'https://www.munchmakers.com',
    stats: 'Thousands of cannabis businesses served, 150+ customizable products',
  },
  {
    id: 'sequinswag',
    name: 'SequinSwag',
    description: 'Fully custom sequin-based products, from dazzling shirts to personalized pillows.',
    fullDescription: `SequinSwag creates fully custom sequin-based products, from dazzling shirts to personalized pillows, delighting both individual customers and businesses alike. If it sparkles and shines, SequinSwag makes it personal.

Our sequin products feature reversible designs that create interactive, eye-catching displays perfect for events, promotions, gifts, and brand activation. Each product is crafted with quality materials and attention to detail.`,
    website: 'https://www.sequinswag.com',
    stats: 'Custom sequin products for consumers and businesses',
  },
];
