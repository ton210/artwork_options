const bcrypt = require('bcryptjs');

function isAuthenticated(req, res, next) {
  if (req.session && req.session.isAdmin) {
    return next();
  }

  res.redirect('/admin/login');
}

async function checkAdminCredentials(username, password) {
  const adminUsername = process.env.ADMIN_USERNAME || 'admin';
  const adminPassword = process.env.ADMIN_PASSWORD;

  if (!adminPassword) {
    throw new Error('ADMIN_PASSWORD not configured');
  }

  // Simple comparison (in production, use hashed passwords)
  if (username === adminUsername && password === adminPassword) {
    return true;
  }

  return false;
}

module.exports = {
  isAuthenticated,
  checkAdminCredentials
};
