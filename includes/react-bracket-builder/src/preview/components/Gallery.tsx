import React, { useState } from 'react';

const Gallery = ({ imageUrls }) => {
  const [currentIndex, setCurrentIndex] = useState(0);

  const handlePrevious = () => {
    setCurrentIndex((prevIndex) => (prevIndex === 0 ? imageUrls.length - 1 : prevIndex - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prevIndex) => (prevIndex === imageUrls.length - 1 ? 0 : prevIndex + 1));
  };

  const galleryStyle = {
    // display: 'flex',
    // alignItems: 'center',
    // justifyContent: 'center',
    position: 'relative',
  };

  const arrowLeftStyle = {
    position: 'absolute',
    top: '50%',
    left: '0',
    transform: 'translateY(-50%)',
    backgroundColor: '#ffffff',
    border: 'none',
    color: '#333333',
    fontSize: '2rem',
    padding: '0.5rem',
    cursor: 'pointer',
    background: 'none'
  };

  const arrowRightStyle = {
    position: 'absolute',
    top: '50%',
    right: '0',
    transform: 'translateY(-50%)',
    backgroundColor: '#ffffff',
    border: 'none',
    color: '#333333',
    fontSize: '2rem',
    padding: '0.5rem',
    cursor: 'pointer',
    background: 'none'
  };

  const imageStyle = {
    maxWidth: '100%',
    height: 'auto',
  };

  return (
      <div className="woocommerce-product-gallery woocommerce-product-gallery--without-images woocommerce-product-gallery--columns-4 images">
        <div className="woocommerce-product-gallery__wrapper" style={galleryStyle} >
          <button style={arrowLeftStyle} onClick={handlePrevious}>
            &lt;
          </button>
          <img className="wp-post-image" style={imageStyle} src={imageUrls[currentIndex]} alt={`Image ${currentIndex + 1}`} />
          <button style={arrowRightStyle} onClick={handleNext}>
            &gt;
          </button>
        </div>
      </div>
  );
};

export default Gallery;
