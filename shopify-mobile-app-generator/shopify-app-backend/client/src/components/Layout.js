import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import {
  Frame,
  Navigation,
  TopBar,
  Toast,
  Loading,
  SkeletonPage
} from '@shopify/polaris';
import {
  HomeIcon,
  TemplateIcon,
  EditIcon,
  ViewIcon,
  SettingsIcon,
  MobileIcon
} from '@shopify/polaris-icons';

function Layout({ children }) {
  const location = useLocation();
  const navigate = useNavigate();
  const [isUserMenuOpen, setIsUserMenuOpen] = React.useState(false);
  const [isSearchActive, setIsSearchActive] = React.useState(false);
  const [searchValue, setSearchValue] = React.useState('');
  const [toastActive, setToastActive] = React.useState(false);

  const handleSearchResultsDismiss = React.useCallback(() => {
    setIsSearchActive(false);
    setSearchValue('');
  }, []);

  const handleSearchFieldChange = React.useCallback((value) => {
    setSearchValue(value);
    setIsSearchActive(value.length > 0);
  }, []);

  const toggleIsUserMenuOpen = React.useCallback(
    () => setIsUserMenuOpen((isUserMenuOpen) => !isUserMenuOpen),
    [],
  );

  const navigationMarkup = (
    <Navigation location={location.pathname}>
      <Navigation.Section
        items={[
          {
            url: '/',
            label: 'Dashboard',
            icon: HomeIcon,
            selected: location.pathname === '/',
            onClick: () => navigate('/')
          },
          {
            url: '/templates',
            label: 'Templates',
            icon: TemplateIcon,
            selected: location.pathname === '/templates',
            onClick: () => navigate('/templates')
          },
          {
            url: '/builder',
            label: 'App Builder',
            icon: EditIcon,
            selected: location.pathname === '/builder',
            onClick: () => navigate('/builder')
          },
          {
            url: '/preview',
            label: 'Preview',
            icon: ViewIcon,
            selected: location.pathname === '/preview',
            onClick: () => navigate('/preview')
          },
          {
            url: '/settings',
            label: 'Settings',
            icon: SettingsIcon,
            selected: location.pathname === '/settings',
            onClick: () => navigate('/settings')
          }
        ]}
      />
    </Navigation>
  );

  const topBarMarkup = (
    <TopBar
      showNavigationToggle
      userMenu={
        <TopBar.UserMenu
          actions={[
            {
              items: [
                { content: 'Help Center', url: '/help' },
                { content: 'Community', url: '/community' }
              ]
            }
          ]}
          name="Merchant"
          detail="Mobile App Builder"
          initials="M"
          open={isUserMenuOpen}
          onToggle={toggleIsUserMenuOpen}
        />
      }
      searchResultsVisible={isSearchActive}
      searchField={
        <TopBar.SearchField
          onChange={handleSearchFieldChange}
          value={searchValue}
          placeholder="Search"
          showFocusBorder
        />
      }
      searchResults={
        <div style={{ padding: '16px' }}>
          <p>Search functionality coming soon...</p>
        </div>
      }
      onSearchResultsDismiss={handleSearchResultsDismiss}
    />
  );

  const toastMarkup = toastActive ? (
    <Toast content="Changes saved" onDismiss={() => setToastActive(false)} />
  ) : null;

  return (
    <Frame
      topBar={topBarMarkup}
      navigation={navigationMarkup}
      showMobileNavigation
      onNavigationDismiss={() => {}}
    >
      {children}
      {toastMarkup}
    </Frame>
  );
}

export default Layout;