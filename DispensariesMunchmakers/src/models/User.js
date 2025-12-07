const db = require('../config/database');
const bcrypt = require('bcryptjs');
const crypto = require('crypto');

const SALT_ROUNDS = 10;

class User {
  /**
   * Create a new user
   */
  static async create(email, password, name) {
    const passwordHash = await bcrypt.hash(password, SALT_ROUNDS);
    const verificationToken = crypto.randomBytes(32).toString('hex');

    const result = await db.query(
      `INSERT INTO users (email, password_hash, name, verification_token, created_at, updated_at)
       VALUES ($1, $2, $3, $4, NOW(), NOW())
       RETURNING id, email, name, is_verified, created_at`,
      [email.toLowerCase(), passwordHash, name, verificationToken]
    );

    return { user: result.rows[0], verificationToken };
  }

  /**
   * Find user by email
   */
  static async findByEmail(email) {
    const result = await db.query(
      'SELECT * FROM users WHERE email = $1',
      [email.toLowerCase()]
    );
    return result.rows[0];
  }

  /**
   * Find user by ID
   */
  static async findById(id) {
    const result = await db.query(
      'SELECT id, email, name, is_verified, created_at FROM users WHERE id = $1',
      [id]
    );
    return result.rows[0];
  }

  /**
   * Verify password
   */
  static async verifyPassword(email, password) {
    const user = await this.findByEmail(email);
    if (!user) return null;

    const isValid = await bcrypt.compare(password, user.password_hash);
    if (!isValid) return null;

    // Return user without password hash
    const { password_hash, ...userWithoutPassword } = user;
    return userWithoutPassword;
  }

  /**
   * Verify email with token
   */
  static async verifyEmail(token) {
    const result = await db.query(
      `UPDATE users
       SET is_verified = true, verification_token = NULL, updated_at = NOW()
       WHERE verification_token = $1
       RETURNING id, email, name, is_verified`,
      [token]
    );

    return result.rows[0];
  }

  /**
   * Update user profile
   */
  static async updateProfile(userId, data) {
    const { name } = data;

    const result = await db.query(
      `UPDATE users
       SET name = $1, updated_at = NOW()
       WHERE id = $2
       RETURNING id, email, name, is_verified`,
      [name, userId]
    );

    return result.rows[0];
  }

  /**
   * Change password
   */
  static async changePassword(userId, newPassword) {
    const passwordHash = await bcrypt.hash(newPassword, SALT_ROUNDS);

    await db.query(
      'UPDATE users SET password_hash = $1, updated_at = NOW() WHERE id = $2',
      [passwordHash, userId]
    );
  }

  /**
   * Generate password reset token
   */
  static async generateResetToken(email) {
    const resetToken = crypto.randomBytes(32).toString('hex');
    const expiresAt = new Date(Date.now() + 3600000); // 1 hour

    const result = await db.query(
      `UPDATE users
       SET reset_token = $1, reset_token_expires = $2, updated_at = NOW()
       WHERE email = $3
       RETURNING id, email, name`,
      [resetToken, expiresAt, email.toLowerCase()]
    );

    return { user: result.rows[0], resetToken };
  }

  /**
   * Reset password with token
   */
  static async resetPassword(token, newPassword) {
    const passwordHash = await bcrypt.hash(newPassword, SALT_ROUNDS);

    const result = await db.query(
      `UPDATE users
       SET password_hash = $1, reset_token = NULL, reset_token_expires = NULL, updated_at = NOW()
       WHERE reset_token = $2 AND reset_token_expires > NOW()
       RETURNING id, email, name`,
      [passwordHash, token]
    );

    return result.rows[0];
  }

  /**
   * Add favorite dispensary
   */
  static async addFavorite(userId, dispensaryId) {
    try {
      await db.query(
        'INSERT INTO user_favorites (user_id, dispensary_id) VALUES ($1, $2)',
        [userId, dispensaryId]
      );
      return true;
    } catch (error) {
      if (error.code === '23505') { // Already exists
        return false;
      }
      throw error;
    }
  }

  /**
   * Remove favorite dispensary
   */
  static async removeFavorite(userId, dispensaryId) {
    const result = await db.query(
      'DELETE FROM user_favorites WHERE user_id = $1 AND dispensary_id = $2',
      [userId, dispensaryId]
    );
    return result.rowCount > 0;
  }

  /**
   * Get user's favorite dispensaries
   */
  static async getFavorites(userId) {
    const result = await db.query(
      `SELECT d.*, uf.created_at as favorited_at
       FROM user_favorites uf
       JOIN dispensaries d ON uf.dispensary_id = d.id
       WHERE uf.user_id = $1 AND d.is_active = true
       ORDER BY uf.created_at DESC`,
      [userId]
    );
    return result.rows;
  }

  /**
   * Check if dispensary is favorited
   */
  static async isFavorited(userId, dispensaryId) {
    const result = await db.query(
      'SELECT 1 FROM user_favorites WHERE user_id = $1 AND dispensary_id = $2',
      [userId, dispensaryId]
    );
    return result.rows.length > 0;
  }
}

module.exports = User;
