import React from 'react'

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    });
};

export default function Episode({title, description, season, episode, airDate}) {
    return (
        <div className= "component" id = "episode">
            <div id="episodeHeader">
                <h4>{title}</h4>
                <p id = "episodeDetails">Season: {season} Episode: {episode}<br/>{formatDate(airDate.date)}</p>
            </div>
            <p>{description}</p>
        </div>
    )
}
