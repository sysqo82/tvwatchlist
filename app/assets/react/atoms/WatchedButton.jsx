import React from 'react'

export default function WatchedButton({id, refreshState}) {
    const handleClick = () => {
        // Add confirmation dialog to prevent accidental clicks
        if (!window.confirm('Mark this episode as watched?')) {
            return;
        }

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
                    episodeTitle: episode.title,
                    airDate: episode.airDate,
                    universe: episode.universe ?? null,
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
                className="btn btn-lg btn-block btn-success" 
                type="button" 
                onClick={handleClick}
            >
                Watched Episode
            </button>
        </div>
    )
}
