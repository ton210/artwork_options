export interface Service {
  id: string;
  title: string;
  shortDescription: string;
  fullDescription: string;
  icon: string;
  features: string[];
  path: string;
}

export interface App {
  id: string;
  name: string;
  tagline: string;
  description: string;
  platforms: string[];
  features: string[];
  website?: string;
  appStoreUrl?: string;
  image: string;
  screenshots?: string[];
  path: string;
}

export interface TeamMember {
  id: string;
  name: string;
  title: string;
  bio: string;
  photo: string;
  linkedin: string;
  order: number;
}

export interface NavLink {
  href: string;
  label: string;
}
