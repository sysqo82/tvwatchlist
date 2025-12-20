import React from 'react'

export default function WatchedButton({id, refreshState, size = "lg", className = ""}) {
    const handleClick = () => {
        const watchedEpisode = fetch('/api/episodes/' + id, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json"
            },
            body: JSON.stringify({
                watched: true
            })
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        });

        watchedEpisode.then(episode => {
            let date = new Date().toUTCString();
            return fetch('/api/histories', {
                method: "POST",
                headers: {
                    "Content-Type": "application/ld+json"
                },
                body: JSON.stringify({
                    seriesTitle: episode.seriesTitle,
                    tvdbSeriesId: episode.tvdbSeriesId,
                    episodeTitle: episode.title,
                    episodeDescription: episode.description,
                    season: episode.season,
                    episode: episode.episode,
                    episodeId: episode.id.toString(),
                    airDate: episode.airDate,
                    poster: episode.poster,
                    watchedAt: date
                })
            });
        }).finally(() => {
            refreshState();
        });
    };

    return (
        <div className="component text-center" id="watched">
            <button 
                className={`btn btn-${size} ${size === 'lg' ? 'btn-block' : ''} btn-success ${className}`}
                type="button" 
                onClick={handleClick}
            >
                Watched
            </button>
        </div>
    )
}
