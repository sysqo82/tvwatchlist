import React from 'react';
import Episode from "../atoms/Episode";
import Show from "../atoms/Show";
import WatchedButton from "../atoms/WatchedButton";
import ShowPoster from "../atoms/ShowPoster";
import RemoveButton from "../atoms/RemoveButton";

export default function ShowUpNext({episodeData, refreshState}) {
    return (
        <div key={episodeData.id}>
            <div className= "component" id="secondary">
                <Show
                    title={episodeData.seriesTitle}
                />
            </div>
            <div className= "component" id="primary">
                <ShowPoster
                    image={episodeData.poster}
                    title={episodeData.seriesTitle}
                />
                <Episode
                    airDate={episodeData.airDate}
                    title={episodeData.title}
                    description={episodeData.description}
                    episode={episodeData.episode}
                    season={episodeData.season}
                />
            </div>
            <WatchedButton id={episodeData.id} refreshState={refreshState}/>
            <RemoveButton id={episodeData.tvdbSeriesId} refreshState={refreshState}/>
        </div>
    )
}
