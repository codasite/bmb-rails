import React from 'react';
import './loader.scss';

const Loader = () => {
  return (
    <div className='loader-container'>
      <div className='loader'></div>
      <p className='loader-text'>Loading...</p>
    </div>
  );
};

export default Loader;
