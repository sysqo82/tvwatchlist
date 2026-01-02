import React, {useState, useEffect} from 'react'

export default function IngestForm({id, type = 'series'}) {

    const [ingestState, setIngestState ] = useState("Start");
    const [ingestDisabled, setIngestDisabled ] = useState('');
    const [ingestSeason, setIngestSeason ] = useState(1);
    const [ingestEpisode, setIngestEpisode ] = useState(1);
    const [tvdbNetwork, setTvdbNetwork ] = useState(null);
    const [networkLoading, setNetworkLoading ] = useState(true);
    const [platform, setPlatform] = useState('Plex');
    
    const isMovie = type === 'movie';

    // Fetch network information from TVDB (only for series)
    useEffect(() => {
        if (isMovie) {
            setNetworkLoading(false);
            return;
        }
        
        fetch(`/api/series/${id}/network`)
            .then(response => response.json())
            .then(data => {
                setTvdbNetwork(data.network);
                setNetworkLoading(false);
            })
            .catch(error => {
                console.log('Error fetching network data:', error);
                setNetworkLoading(false);
            });
    }, [id, isMovie]);



    function ingestShow(id) {
        if (isMovie) {
            console.log("Ingesting movie " + id);
            setIngestState('Ingesting...');
            fetch('/api/tvdb/movie/ingest',{
                method: "POST",
                headers: {
                    "Content-Type": "application/json+ld"
                },
                body: JSON.stringify({
                    movieId: id,
                    platform: platform
                })
            })
            .then((response) => {
                if(!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((ingestData) => {
                console.log("Ingested movie " + ingestData);
                setIngestState("Ingested");
                setIngestDisabled("disabled");
            })
            .catch((err) => {
                console.log("Error ingesting movie " + err.message);
                setIngestState("Error during Ingest");
                setIngestDisabled("disabled");
            });
        } else {
            console.log("Ingesting show " + id + " season " + ingestSeason + " episode " + ingestEpisode);
            setIngestState('Ingesting...');
            fetch('/api/tvdb/series/ingest',{
                method: "POST",
                headers: {
                    "Content-Type": "application/json+ld"
                },
                body: JSON.stringify({
                    seriesId: id,
                    season: Number(ingestSeason),
                    episode: Number(ingestEpisode)
                })
            })
            .then((response) => {
                if(!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((ingestData) => {
                console.log("Ingested show " + ingestData);
                setIngestState("Ingested");
                setIngestDisabled("disabled");
            })
            .catch((err) => {
                console.log("Error ingesting show " + err.message);
                setIngestState("Error during Ingest");
                setIngestDisabled("disabled");
            });
        }
    }

    return (
        <div className="ingestForm">
            {isMovie && (
                <div className="movie-metadata mb-3">
                    <div className="mb-2">
                        <label htmlFor={id + "platform"} className="form-label text-muted">Platform:</label>
                        <input 
                            name={id + "platform"} 
                            type="text" 
                            placeholder="Plex" 
                            id="platform"
                            className="form-control"
                            value={platform}
                            onChange={(e) => setPlatform(e.target.value)}
                        />
                    </div>
                </div>
            )}
            {!isMovie && (
                <div className="partialIngest mb-3">
                    <div className="partialIngestInput">
                        <label htmlFor={id + "season"}>Season: </label>
                        <input name={id + "season"} type={"number"} placeholder={"1"} id={"season"} onChange={(e) => setIngestSeason(e.target.value)}></input>
                    </div>
                    <div className="partialIngestInput">
                        <label htmlFor={id + "season"}>Episode: </label>
                        <input name={id + "episode"} type={"number"} placeholder={"1"} id={"episode"} onChange={(e) => setIngestEpisode(e.target.value)}></input>
                    </div>
                </div>
            )}
            {tvdbNetwork && !isMovie && (
                <div className="network-info mb-3">
                    <span className="badge bg-primary">
                        Original Network: {tvdbNetwork}
                    </span>
                </div>
            )}
            <button className={"btn btn-lg w-100 btn-dark " + ingestDisabled} type="button" onClick={() => ingestShow(id)}>
                {ingestState}
            </button>
        </div>
    )
}
