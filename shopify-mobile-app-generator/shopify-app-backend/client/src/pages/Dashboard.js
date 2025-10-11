import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from 'react-query';
import {
  Page,
  Layout,
  Card,
  Button,
  Stack,
  TextContainer,
  DisplayText,
  Heading,
  Badge,
  ProgressBar,
  EmptyState,
  Spinner,
  Banner
} from '@shopify/polaris';
import { MobileIcon, EditIcon } from '@shopify/polaris-icons';
import { fetchAppConfig, fetchBuildStatus } from '../utils/api';

function Dashboard() {
  const navigate = useNavigate();
  
  const { data: appConfig, isLoading: configLoading } = useQuery(
    'appConfig',
    fetchAppConfig,
    { refetchInterval: 5000 }
  );
  
  const { data: buildStatus, isLoading: buildLoading } = useQuery(
    ['buildStatus', appConfig?.lastBuild?.id],
    () => fetchBuildStatus(appConfig.lastBuild.id),
    {
      enabled: !!appConfig?.lastBuild?.id,
      refetchInterval: appConfig?.lastBuild?.status === 'processing' ? 2000 : false
    }
  );

  if (configLoading) {
    return (
      <Page title="Dashboard">
        <div style={{ textAlign: 'center', padding: '60px' }}>
          <Spinner size="large" />
        </div>
      </Page>
    );
  }

  const hasConfiguration = appConfig && appConfig.appName;
  const hasActiveBuild = appConfig?.lastBuild && appConfig.lastBuild.status !== 'failed';

  return (
    <Page
      title="Mobile App Dashboard"
      primaryAction={{
        content: hasConfiguration ? 'Edit App' : 'Get Started',
        icon: EditIcon,
        onAction: () => navigate(hasConfiguration ? '/builder' : '/templates')
      }}
    >
      <Layout>
        <Layout.Section>
          {!hasConfiguration && (
            <Banner
              title="Welcome to Mobile App Builder"
              status="info"
              action={{ content: 'Choose Template', onAction: () => navigate('/templates') }}
            >
              <p>Transform your Shopify store into a mobile app in minutes. Choose from professional templates, customize with drag-and-drop blocks, and generate your APK file.</p>
            </Banner>
          )}
        </Layout.Section>

        <Layout.Section>
          <Layout.TwoThirds>
            {hasConfiguration ? (
              <Card>
                <Card.Section>
                  <Stack distribution="equalSpacing" alignment="center">
                    <Stack vertical spacing="tight">
                      <DisplayText size="medium">{appConfig.appName}</DisplayText>
                      <p>Template: {appConfig.template || 'Modern'}</p>
                    </Stack>
                    <Badge status={hasActiveBuild ? 'success' : 'attention'}>
                      {hasActiveBuild ? 'Configured' : 'Needs Setup'}
                    </Badge>
                  </Stack>
                </Card.Section>
                
                <Card.Section>
                  <Stack distribution="fill" spacing="loose">
                    <Button onClick={() => navigate('/builder')}>Edit Design</Button>
                    <Button onClick={() => navigate('/preview')}>Preview App</Button>
                    <Button primary onClick={() => navigate('/settings')}>Build APK</Button>
                  </Stack>
                </Card.Section>
              </Card>
            ) : (
              <EmptyState
                heading="Create your first mobile app"
                action={{
                  content: 'Choose Template',
                  onAction: () => navigate('/templates')
                }}
                image="/empty-state-mobile.svg"
              >
                <p>Start by selecting a template that matches your brand, then customize it with your products and branding.</p>
              </EmptyState>
            )}
          </Layout.TwoThirds>

          <Layout.OneThird>
            <Card title="Build Status">
              <Card.Section>
                {buildLoading ? (
                  <div style={{ textAlign: 'center', padding: '20px' }}>
                    <Spinner size="small" />
                  </div>
                ) : buildStatus ? (
                  <Stack vertical spacing="tight">
                    <Stack distribution="equalSpacing" alignment="center">
                      <span>Latest Build</span>
                      <Badge 
                        status={
                          buildStatus.status === 'completed' ? 'success' :
                          buildStatus.status === 'failed' ? 'critical' :
                          'attention'
                        }
                      >
                        {buildStatus.status}
                      </Badge>
                    </Stack>
                    
                    {buildStatus.status === 'processing' && (
                      <>
                        <ProgressBar progress={buildStatus.progress || 0} size="small" />
                        <p>{buildStatus.message}</p>
                      </>
                    )}
                    
                    {buildStatus.status === 'completed' && (
                      <Button
                        primary
                        fullWidth
                        onClick={() => window.open(`/api/app/download-apk/${buildStatus.id}`, '_blank')}
                      >
                        Download APK
                      </Button>
                    )}
                    
                    {buildStatus.status === 'failed' && (
                      <TextContainer>
                        <p style={{ color: 'red' }}>{buildStatus.error}</p>
                        <Button onClick={() => navigate('/settings')}>Try Again</Button>
                      </TextContainer>
                    )}
                  </Stack>
                ) : (
                  <TextContainer>
                    <p>No builds yet</p>
                    <Button disabled={!hasConfiguration} onClick={() => navigate('/settings')}>
                      Create First Build
                    </Button>
                  </TextContainer>
                )}
              </Card.Section>
            </Card>

            <div style={{ marginTop: '20px' }}>
              <Card title="Quick Stats">
                <Card.Section>
                  <Stack vertical spacing="loose">
                    <Stack distribution="equalSpacing">
                      <span>Downloads</span>
                      <strong>{appConfig?.downloads || 0}</strong>
                    </Stack>
                    <Stack distribution="equalSpacing">
                      <span>Installs</span>
                      <strong>{appConfig?.installs || 0}</strong>
                    </Stack>
                  </Stack>
                </Card.Section>
              </Card>
            </div>
          </Layout.OneThird>
        </Layout.Section>

        <Layout.Section>
          <Card title="Getting Started">
            <Card.Section>
              <Stack vertical spacing="loose">
                <TextContainer>
                  <Heading>How it works:</Heading>
                  <ol>
                    <li>Choose a template that matches your brand</li>
                    <li>Customize colors, layout, and content blocks</li>
                    <li>Preview your app on different devices</li>
                    <li>Generate and download your APK file</li>
                    <li>Upload to Google Play Store with our instructions</li>
                  </ol>
                </TextContainer>
                
                <Stack>
                  <Button onClick={() => navigate('/templates')}>View Templates</Button>
                  <Button plain onClick={() => window.open('/help', '_blank')}>
                    View Documentation
                  </Button>
                </Stack>
              </Stack>
            </Card.Section>
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}

export default Dashboard;