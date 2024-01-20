import React from 'react'
import Moment from 'moment'

export default function Episode({title, description, season, episode, airDate}) {
    return (
        <div className= "component" id = "episode">
            <div id="episodeHeader">
                <h4>{title}</h4>
                <p id = "episodeDetails">Season: {season} Episode: {episode}<br/>{Moment(airDate.date).format('Do MMM YYYY')}</p>
            </div>
            <p>{description}</p>
        </div>
    )
}
