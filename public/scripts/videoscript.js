import videoList from './videolist.js';

const videoPlayer = document.getElementById('videoPlayer');
const playBtn = document.getElementById('playButton');
const pauzeBtn =   document.getElementById('pauseButton');
const fullScreenBtn = document.getElementById('fullscreenButton');
const shuffleBtn = document.getElementById('shuffleknop');
const videoIndex = 'videoLijst';
let controlsBound = false;

const getList = () => {
    return JSON.parse(localStorage.getItem(videoIndex)) || []
}

const setList = (list) => {
    localStorage.setItem(videoIndex, JSON.stringify(list ?? []));
}

const bindControlsOnce = () => {
    if (controlsBound) return;
    controlsBound = true;
    playBtn?.addEventListener('click', () => videoPlayer.play());
    pauzeBtn?.addEventListener('click', () => videoPlayer.pause());
    shuffleBtn?.addEventListener('click', () => speelVideos(true, true));
    fullScreenBtn?.addEventListener('click', () => videoPlayer.requestFullscreen?.().catch?.(() => {}));
}

const getDefaultvideoList = (myVideoList, max = 8, level = '1') => {
    console.warn("selectionpage list was empty, rendering defaultlist");
    const themes = Object.keys(myVideoList); //array van de keys
    const pick = new Set();

    const poolSize = themes.reduce(
        (n, t) => n + ((myVideoList[t][level] || []).length),
        0
    );

    if (poolSize === 0) return [];

    while (pick.size < Math.min(max, poolSize)) {
        const theme = themes[Math.floor(Math.random() * themes.length)];
        const list = myVideoList[theme][level] || [];
        if (list.length === 0) continue;
            const item = list[Math.floor(Math.random() * list.length)];
            pick.add(item);
    }
        
    return [...pick];
}


const getPath = (relPath) => {
    return (
        './videos/' +
        String(relPath)
            .replace(/^[/.\\]+/, '')
            .replace(/\\/g, '/')
            .replace(/\/{2,}/g, '/')
    )
}



function speelVideos(shuffle = false, isIntro = true) {
    const introVideo = 'inout/intro.mp4';
    const outroVideo = 'inout/outro.mp4';
    let videoLijst = getList() ?? [];

    if (videoLijst.length === 0) {
        console.log("empty videolist");
        return;
    }

    // Voeg intro en outro toe aan de lijst
    if (isIntro) {
        videoLijst = videoLijst.filter(video => (video !== introVideo && video !== outroVideo));
        videoLijst.unshift(introVideo);
        videoLijst.push(outroVideo);
    } else {
        videoLijst = videoLijst.filter(video => video !== introVideo);
    }

    if (shuffle && videoLijst.length > 2) {
        const middleVideos = videoLijst.slice(1, -1).sort(() => Math.random() - 0.5);
        videoLijst = [introVideo, ...middleVideos, outroVideo];
    }

    setList(videoLijst);
    playCurrent(videoLijst);

}

function playCurrent(list) {
    if (!Array.isArray(list) || list.length === 0) {
        console.log("Playlist was empty");
        return;
    }

    videoPlayer.pause();
    videoPlayer.removeAttribute('src');
    videoPlayer.load();
    videoPlayer.src = getPath(list[0]);
    videoPlayer.load();
    videoPlayer.play().catch((err) => console.warn('play failed: ', err?.message || err));

    if (list.length > 1){
        const nextVideoPlayer = document.createElement('video');
        const source = document.createElement('source');
    
        source.src = getPath(list[1]);
        source.type = 'video/mp4';

        nextVideoPlayer.appendChild(source);
        nextVideoPlayer.preload = 'auto';
        nextVideoPlayer.load();
    }

    videoPlayer.onended = function() {
        const newList = list.slice(1);
        setList(newList);

        if (newList.length > 0) {
            playCurrent(newList)
        } else {
            console.log("Playlist is empty");
        }
    };


}

window.onload = function() {
    bindControlsOnce();
    const list = getList() ?? [];
    if (list.length === 0) {
        const defaultList = getDefaultvideoList(videoList, 8, '1') ?? [];
        localStorage.setItem(videoIndex, JSON.stringify(defaultList))
    }
    speelVideos();
};

document.addEventListener('wheel', function(event) {
    if (event.ctrlKey) { event.preventDefault(); }}, { passive: false });

