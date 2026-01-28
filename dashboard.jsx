import React from 'react';

const Dashboard = () => {
  return (
    <div style={{padding: '20px'}}>
      <h1 style={{color: 'blue'}}>Admin Dashboard</h1>
      <p>Welcome to Community Donation System</p>
      
      <div style={{
        display: 'flex',
        gap: '20px',
        marginTop: '30px'
      }}>
        <div style={{
          background: '#e6f7ff',
          padding: '20px',
          borderRadius: '10px',
          width: '200px'
        }}>
          <h3>Total Donations</h3>
          <p style={{
            fontSize: '32px',
            fontWeight: 'bold',
            color: '#1890ff'
          }}>1,250</p>
        </div>
        
        <div style={{
          background: '#f6ffed',
          padding: '20px',
          borderRadius: '10px',
          width: '200px'
        }}>
          <h3>Total Requests</h3>
          <p style={{
            fontSize: '32px',
            fontWeight: 'bold',
            color: '#52c41a'
          }}>890</p>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
