import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from 'react-query';
import {
  Page,
  Layout,
  Card,
  Stack,
  Button,
  Heading,
  TextContainer,
  Thumbnail,
  Badge,
  Modal,
  Loading,
  Banner
} from '@shopify/polaris';
import { ChevronRightIcon } from '@shopify/polaris-icons';
import { fetchTemplates, fetchAppConfig, updateAppConfig } from '../utils/api';

function TemplateSelector() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  
  const [selectedTemplate, setSelectedTemplate] = useState(null);
  const [previewModalActive, setPreviewModalActive] = useState(false);

  const { data: templates, isLoading } = useQuery('templates', fetchTemplates);
  const { data: appConfig } = useQuery('appConfig', fetchAppConfig);

  const updateConfigMutation = useMutation(updateAppConfig, {
    onSuccess: () => {
      queryClient.invalidateQueries('appConfig');
      navigate('/builder');
    }
  });

  const handleSelectTemplate = (template) => {
    setSelectedTemplate(template);
    setPreviewModalActive(true);
  };

  const handleConfirmTemplate = () => {
    const configUpdate = {
      template: selectedTemplate.id,
      primaryColor: selectedTemplate.colors.primary,
      secondaryColor: selectedTemplate.colors.secondary,
      accentColor: selectedTemplate.colors.accent,
      textColor: selectedTemplate.colors.text,
      backgroundColor: selectedTemplate.colors.background,
      features: selectedTemplate.features,
      layout: {
        homeBlocks: selectedTemplate.defaultBlocks,
        categoryLayout: 'grid',
        productLayout: 'standard'
      }
    };

    // If app name doesn't exist, set a default
    if (!appConfig?.appName) {
      configUpdate.appName = 'My Store App';
    }

    updateConfigMutation.mutate(configUpdate);
  };

  if (isLoading) {
    return <Loading />;
  }

  return (
    <Page
      title="Choose Your Template"
      subtitle="Select a design that matches your brand and customize it to your needs"
      breadcrumbs={[{ content: 'Dashboard', url: '/' }]}
    >
      <Layout>
        <Layout.Section>
          <Banner status="info">
            <p>Templates include pre-configured layouts, color schemes, and features. You can customize everything after selection.</p>
          </Banner>
        </Layout.Section>

        <Layout.Section>
          <div style={{ 
            display: 'grid', 
            gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', 
            gap: '20px' 
          }}>
            {templates?.map((template) => (
              <Card key={template.id}>
                <div style={{ position: 'relative' }}>
                  <Thumbnail
                    source={template.preview || '/template-preview-placeholder.png'}
                    alt={template.name}
                    size="large"
                  />
                  {appConfig?.template === template.id && (
                    <div style={{
                      position: 'absolute',
                      top: '10px',
                      right: '10px'
                    }}>
                      <Badge status="success">Current</Badge>
                    </div>
                  )}
                </div>
                
                <Card.Section>
                  <Stack vertical spacing="tight">
                    <Heading>{template.name}</Heading>
                    <p>{template.description}</p>
                    
                    <Stack distribution="equalSpacing" alignment="center">
                      <Button 
                        plain 
                        onClick={() => handleSelectTemplate(template)}
                      >
                        Preview
                      </Button>
                      <Button
                        primary={appConfig?.template !== template.id}
                        disabled={appConfig?.template === template.id}
                        onClick={() => handleSelectTemplate(template)}
                        icon={ChevronRightIcon}
                      >
                        {appConfig?.template === template.id ? 'Selected' : 'Select'}
                      </Button>
                    </Stack>
                  </Stack>
                </Card.Section>
              </Card>
            ))}
          </div>
        </Layout.Section>
      </Layout>

      {/* Template Preview Modal */}
      <Modal
        open={previewModalActive}
        onClose={() => setPreviewModalActive(false)}
        title={selectedTemplate?.name}
        primaryAction={{
          content: 'Use This Template',
          onAction: handleConfirmTemplate,
          loading: updateConfigMutation.isLoading
        }}
        secondaryActions={[
          {
            content: 'Cancel',
            onAction: () => setPreviewModalActive(false)
          }
        ]}
        large
      >
        <Modal.Section>
          {selectedTemplate && (
            <Stack vertical spacing="loose">
              <div style={{
                display: 'flex',
                justifyContent: 'center',
                padding: '20px',
                backgroundColor: '#f9f9f9',
                borderRadius: '8px'
              }}>
                <Thumbnail
                  source={selectedTemplate.preview || '/template-preview-placeholder.png'}
                  alt={selectedTemplate.name}
                  size="large"
                />
              </div>
              
              <TextContainer>
                <p>{selectedTemplate.description}</p>
                
                <Heading element="h3">Features Included:</Heading>
                <ul>
                  {selectedTemplate.features?.reviews && <li>Customer Reviews</li>}
                  {selectedTemplate.features?.wishlist && <li>Wishlist</li>}
                  {selectedTemplate.features?.pushNotifications && <li>Push Notifications</li>}
                  {selectedTemplate.features?.socialLogin && <li>Social Login</li>}
                  {selectedTemplate.features?.guestCheckout && <li>Guest Checkout</li>}
                </ul>
                
                <Heading element="h3">Default Sections:</Heading>
                <ul>
                  {selectedTemplate.defaultBlocks?.map((block, index) => (
                    <li key={index}>{block.title} ({block.type})</li>
                  ))}
                </ul>
              </TextContainer>

              <div>
                <Heading element="h3">Color Palette:</Heading>
                <Stack spacing="tight">
                  {Object.entries(selectedTemplate.colors || {}).map(([name, color]) => (
                    <div key={name} style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px'
                    }}>
                      <div style={{
                        width: '20px',
                        height: '20px',
                        backgroundColor: color,
                        border: '1px solid #ddd',
                        borderRadius: '4px'
                      }} />
                      <span style={{ textTransform: 'capitalize' }}>{name}</span>
                    </div>
                  ))}
                </Stack>
              </div>
            </Stack>
          )}
        </Modal.Section>
      </Modal>
    </Page>
  );
}

export default TemplateSelector;