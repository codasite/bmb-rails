import React, { useState } from 'react';

const Thumbnails = ({ imageUrls, currentIndex, setCurrentIndex }) => {
    return (
        <div
            className="wpbb-thumbnail-container"
            style={thumbNailsStyle}
        >
            {imageUrls.map((imageUrl, index) => (
                <Thumbnail
                    imageUrl={imageUrl}
                    index={index}
                    currentIndex={currentIndex}
                    setCurrentIndex={setCurrentIndex}
                    />
            ))}

                {/* <img
                    src={imageUrl}
                    key={index}
                    className={`wpbb-thumbnail ${index === currentIndex ? 'wpbb-thumbnail-active' : ''}`}
                    style={humbNailStyle}
                    />
            ))} */}
        </div>
    );
}

const Thumbnail = ({ imageUrl, index, currentIndex, setCurrentIndex }) => {
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
const thumbNailsStyle = {
    display: 'flex',
    flexDirection: 'row',
    //justifyContent: 'center',
}
const thumbNailStyle = {
    height: '100px',
    width: '100px',
    cursor: 'pointer',  
};

const inactiveThumbNailStyle = {
    height: '100px',
    width: '100px',
    opacity: '0.5',
    cursor: 'pointer',
    };