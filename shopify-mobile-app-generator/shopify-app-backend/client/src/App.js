import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { AppProvider } from '@shopify/polaris';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import '@shopify/polaris/build/esm/styles.css';

// Pages
import Dashboard from './pages/Dashboard';
import TemplateSelector from './pages/TemplateSelector';
import AppBuilder from './pages/AppBuilder';
import Preview from './pages/Preview';
import Settings from './pages/Settings';

// Components
import Layout from './components/Layout';

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

function App() {
  return (
    <AppProvider i18n={{}}>
      <QueryClientProvider client={queryClient}>
        <DndProvider backend={HTML5Backend}>
          <Router>
            <Layout>
              <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/templates" element={<TemplateSelector />} />
                <Route path="/builder" element={<AppBuilder />} />
                <Route path="/preview" element={<Preview />} />
                <Route path="/settings" element={<Settings />} />
              </Routes>
            </Layout>
          </Router>
        </DndProvider>
      </QueryClientProvider>
    </AppProvider>
  );
}

export default App;