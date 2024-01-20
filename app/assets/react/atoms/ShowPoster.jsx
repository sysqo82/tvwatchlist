import React from 'react'

export default function Poster({image, title}) {
    return (
        <div className= "component poster-container">
            <img src={image} className="img-fluid" alt={title} />
        </div>
    )
}