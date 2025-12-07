/**
 * Email Service
 * Handles sending transactional emails
 *
 * Note: This is a placeholder implementation
 * In production, integrate with SendGrid, Mailgun, AWS SES, or similar
 */

class EmailService {
  constructor() {
    this.from = process.env.EMAIL_FROM || 'noreply@bestdispensaries.munchmakers.com';
    this.enabled = process.env.EMAIL_ENABLED === 'true';
  }

  /**
   * Send welcome email after registration
   */
  async sendWelcomeEmail(user) {
    if (!this.enabled) {
      console.log('[Email] Welcome email would be sent to:', user.email);
      return;
    }

    const subject = 'Welcome to Top Dispensaries 2026!';
    const html = `
      <h1>Welcome, ${user.name}!</h1>
      <p>Thank you for creating an account at Top Dispensaries 2026.</p>
      <p>You can now:</p>
      <ul>
        <li>Save your favorite dispensaries</li>
        <li>Submit reviews and ratings</li>
        <li>Claim your dispensary listing (if you're an owner)</li>
        <li>Get personalized recommendations</li>
      </ul>
      <p><a href="${process.env.BASE_URL}/account">Visit your account</a></p>
      <p>Happy exploring!</p>
      <p><small>Top Dispensaries 2026 - A MunchMakers Initiative</small></p>
    `;

    return this.send(user.email, subject, html);
  }

  /**
   * Send email verification
   */
  async sendVerificationEmail(user, verificationToken) {
    if (!this.enabled) {
      console.log('[Email] Verification email would be sent to:', user.email);
      console.log('[Email] Verification link:', `${process.env.BASE_URL}/verify/${verificationToken}`);
      return;
    }

    const subject = 'Verify Your Email - Top Dispensaries 2026';
    const verifyUrl = `${process.env.BASE_URL}/verify/${verificationToken}`;
    const html = `
      <h1>Verify Your Email</h1>
      <p>Hi ${user.name},</p>
      <p>Click the link below to verify your email address:</p>
      <p><a href="${verifyUrl}" style="background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;">Verify Email</a></p>
      <p>Or copy this link: ${verifyUrl}</p>
      <p><small>This link will expire in 24 hours.</small></p>
    `;

    return this.send(user.email, subject, html);
  }

  /**
   * Send business claim notification
   */
  async sendClaimNotification(claim, dispensary) {
    if (!this.enabled) {
      console.log('[Email] Claim notification would be sent to:', claim.contact_email);
      return;
    }

    const subject = `Business Claim: ${dispensary.name}`;
    const html = `
      <h1>Business Claim Submitted</h1>
      <p>Hi ${claim.contact_name},</p>
      <p>Your claim for <strong>${dispensary.name}</strong> has been submitted.</p>
      <p>Verification Code: <strong style="font-size: 24px; color: #16a34a;">${claim.verification_code}</strong></p>
      <p>Enter this code at: <a href="${process.env.BASE_URL}/claim/verify">${process.env.BASE_URL}/claim/verify</a></p>
      ${claim.is_verified ? '<p><strong>✓ Auto-verified!</strong> Your email domain matches the dispensary website.</p>' : ''}
      <p>Once verified, our team will review your claim within 1-2 business days.</p>
    `;

    return this.send(claim.contact_email, subject, html);
  }

  /**
   * Send claim approval notification
   */
  async sendClaimApprovalEmail(claim, dispensary) {
    if (!this.enabled) {
      console.log('[Email] Claim approval would be sent to:', claim.contact_email);
      return;
    }

    const subject = `Claim Approved: ${dispensary.name}`;
    const html = `
      <h1>Claim Approved!</h1>
      <p>Hi ${claim.contact_name},</p>
      <p>Congratulations! Your claim for <strong>${dispensary.name}</strong> has been approved.</p>
      <p>You can now:</p>
      <ul>
        <li>Update your dispensary information</li>
        <li>Respond to reviews</li>
        <li>Access analytics dashboard</li>
        <li>Get wholesale pricing on MunchMakers products</li>
      </ul>
      <p><a href="${process.env.BASE_URL}/account" style="background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;">Manage Your Listing</a></p>
    `;

    return this.send(claim.contact_email, subject, html);
  }

  /**
   * Send generic email
   */
  async send(to, subject, html) {
    // TODO: Integrate with email provider (SendGrid, Mailgun, etc.)
    console.log('[Email] Sending email:');
    console.log('  To:', to);
    console.log('  Subject:', subject);
    console.log('  HTML length:', html.length, 'chars');

    // In production, use actual email service:
    /*
    const sgMail = require('@sendgrid/mail');
    sgMail.setApiKey(process.env.SENDGRID_API_KEY);

    await sgMail.send({
      to,
      from: this.from,
      subject,
      html
    });
    */

    return { success: true, message: 'Email queued (dev mode)' };
  }
}

module.exports = new EmailService();
