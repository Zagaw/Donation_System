import React from 'react';
import { FaTachometerAlt, FaUsers, FaDonate, FaSignOutAlt } from 'react-icons/fa';

const Sidebar = () => {
  return (
    <div style={{
      width: '250px',
      background: '#1e40af',
      color: 'white',
      height: '100vh',
      padding: '20px'
    }}>
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        marginBottom: '30px'
      }}>
        <div style={{
          background: 'white',
          width: '40px',
          height: '40px',
          borderRadius: '8px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          <FaDonate style={{ color: '#1e40af', fontSize: '20px' }} />
        </div>
        <div>
          <h2 style={{ margin: 0, fontSize: '18px', fontWeight: 'bold' }}>Community</h2>
          <p style={{ margin: 0, fontSize: '12px', opacity: 0.8 }}>Donation System</p>
        </div>
      </div>

      <nav>
        <button style={{
          width: '100%',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '12px',
          background: '#1e3a8a',
          border: 'none',
          color: 'white',
          borderRadius: '8px',
          marginBottom: '8px',
          cursor: 'pointer'
        }}>
          <FaTachometerAlt />
          <span>Dashboard</span>
        </button>

        <button style={{
          width: '100%',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '12px',
          background: 'transparent',
          border: 'none',
          color: 'white',
          borderRadius: '8px',
          marginBottom: '8px',
          cursor: 'pointer'
        }}>
          <FaUsers />
          <span>View Users</span>
        </button>

        <button style={{
          width: '100%',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '12px',
          background: 'transparent',
          border: 'none',
          color: 'white',
          borderRadius: '8px',
          marginBottom: '8px',
          cursor: 'pointer'
        }}>
          <FaDonate />
          <span>Donations</span>
        </button>
      </nav>

      <div style={{ position: 'absolute', bottom: '20px', width: '210px' }}>
        <button style={{
          width: '100%',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '12px',
          background: 'transparent',
          border: 'none',
          color: 'white',
          borderRadius: '8px',
          cursor: 'pointer'
        }}>
          <FaSignOutAlt />
          <span>Logout</span>
        </button>
      </div>
    </div>
  );
};

export default Sidebar;
