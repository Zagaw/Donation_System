import React from 'react';
import Dashboard from './dashboard.jsx';
import Sidebar from './Sidebar.jsx';
import Header from './Header.jsx';

const App = () => {
  return (
    <div style={{ display: 'flex', minHeight: '100vh' }}>
      <Sidebar />
      <div style={{ flex: 1 }}>
        <Header />
        <Dashboard />
      </div>
    </div>
  );
};

export default App;
