import React, { useState } from 'react';

interface ThumbnailsProps {
    imageUrls: string[];
    currentIndex: number;
    setCurrentIndex: React.Dispatch<React.SetStateAction<number>>;
  }
  
const Thumbnails: React.FC<ThumbnailsProps> = ({ imageUrls, currentIndex, setCurrentIndex }) => {
    return (
        <div className="wpbb-thumbnail-container" style={thumbNailsStyle}>
            {imageUrls.map((imageUrl, index) => (
                <Thumbnail
                    imageUrl={imageUrl}
                    index={index}
                    currentIndex={currentIndex}
                    setCurrentIndex={setCurrentIndex}
                    />
            ))}
        </div>
    );
}

interface ThumbnailProps {
    imageUrl: string;
    index: number;
    currentIndex: number;
    setCurrentIndex: React.Dispatch<React.SetStateAction<number>>;
}

const Thumbnail: React.FC<ThumbnailProps> = ({ imageUrl, index, currentIndex, setCurrentIndex }) => {
    return (
        <img
            src={imageUrl}
            key={index}
            className={`wpbb-thumbnail ${index === currentIndex ? 'wpbb-thumbnail-active' : ''}`}
            style={index === currentIndex ? thumbNailStyle : inactiveThumbNailStyle}
            onClick={() => setCurrentIndex(index)}
            />
    );
}



export default Thumbnails;


// Styles
const thumbNailsStyle: React.CSSProperties = {
    display: 'flex',
    flexDirection: 'row',
};

const thumbNailStyle: React.CSSProperties = {
    height: '130px',
    width: '130px',
    cursor: 'pointer',  
};

const inactiveThumbNailStyle: React.CSSProperties = {
    height: '130px',
    width: '130px',
    opacity: '0.5',
    cursor: 'pointer',
};