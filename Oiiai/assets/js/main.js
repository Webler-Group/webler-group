const scene = new THREE.Scene()
const camera = new THREE.PerspectiveCamera(
    75,
    window.innerWidth / window.innerHeight,
    0.1,
    1000
)
const renderer = new THREE.WebGLRenderer({ antialias: true })
renderer.setSize(window.innerWidth, window.innerHeight)
document.body.appendChild(renderer.domElement)

// Enhanced controls
const controls = new THREE.OrbitControls(camera, renderer.domElement)
controls.enableDamping = true
controls.dampingFactor = 0.05
controls.screenSpacePanning = false
controls.minDistance = 3
controls.maxDistance = 10
controls.maxPolarAngle = Math.PI / 1.5

const audio = new Audio('assets/audio/oiiai.mp3')
audio.addEventListener('canplaythrough', updateLoadingProgress)

audio.loop = true

// Audio analysis setup
let audioContext, analyser, dataArray
function setupAudioAnalysis() {
    audioContext = new (window.AudioContext || window.webkitAudioContext)()
    analyser = audioContext.createAnalyser()
    const source = audioContext.createMediaElementSource(audio)
    source.connect(analyser)
    analyser.connect(audioContext.destination)
    analyser.fftSize = 256
    dataArray = new Uint8Array(analyser.frequencyBinCount)
}

// Enhanced particle system
const particleCount = 2000
const particles = new THREE.BufferGeometry()
const positions = new Float32Array(particleCount * 3)
const colors = new Float32Array(particleCount * 3)
const sizes = new Float32Array(particleCount)

for (let i = 0; i < particleCount; i++) {
    const i3 = i * 3
    positions[i3] = (Math.random() - 0.5) * 10
    positions[i3 + 1] = (Math.random() - 0.5) * 10
    positions[i3 + 2] = (Math.random() - 0.5) * 10

    colors[i3] = Math.random()
    colors[i3 + 1] = Math.random()
    colors[i3 + 2] = Math.random()

    sizes[i] = Math.random() * 0.1
}

particles.setAttribute(
    'position',
    new THREE.BufferAttribute(positions, 3)
)
particles.setAttribute('color', new THREE.BufferAttribute(colors, 3))
particles.setAttribute('size', new THREE.BufferAttribute(sizes, 1))

const particleMaterial = new THREE.PointsMaterial({
    size: 0.05,
    vertexColors: true,
    blending: THREE.AdditiveBlending,
    transparent: true,
    opacity: 0.8,
})

const particleSystem = new THREE.Points(particles, particleMaterial)
scene.add(particleSystem)

// Geometric patterns
const geometricPatterns = new THREE.Group()
const geometryCount = 8
for (let i = 0; i < geometryCount; i++) {
    const geometry = new THREE.TorusGeometry(2 + i * 0.5, 0.1, 16, 100)
    const material = new THREE.MeshPhongMaterial({
        color: new THREE.Color().setHSL(i / geometryCount, 1, 0.5),
        transparent: true,
        opacity: 0.3,
    })
    const ring = new THREE.Mesh(geometry, material)
    geometricPatterns.add(ring)
}
scene.add(geometricPatterns)

// Enhanced lighting
const lightColors = [
    0xff0000, 0x00ff00, 0x0000ff, 0xff00ff, 0xffff00, 0x00ffff,
]
const lights = []

lightColors.forEach((color, index) => {
    const light = new THREE.SpotLight(color, 4)
    light.position.set(
        Math.cos(index * Math.PI * 0.3) * 8,
        3,
        Math.sin(index * Math.PI * 0.3) * 8
    )
    light.angle = Math.PI / 6
    light.penumbra = 0.1
    scene.add(light)
    lights.push(light)
})

const ambientLight = new THREE.AmbientLight(0x404040, 0.5)
scene.add(ambientLight)

camera.position.z = 5

// Motion trails
const trailsGeometry = new THREE.BufferGeometry()
const trailCount = 100
const trailPositions = new Float32Array(trailCount * 3)
trailsGeometry.setAttribute(
    'position',
    new THREE.BufferAttribute(trailPositions, 3)
)
const trailMaterial = new THREE.LineBasicMaterial({
    color: 0xffffff,
    transparent: true,
    opacity: 0.5,
    blending: THREE.AdditiveBlending,
})
const trails = new THREE.Line(trailsGeometry, trailMaterial)
scene.add(trails)

const loadingScreen = document.getElementById('loadingScreen')
const loadingProgress = loadingScreen.querySelector('.loading-progress')
let totalResources = 2 // 3D model and audio
let loadedResources = 0

function updateLoadingProgress() {
    loadedResources++
    const progress = Math.round((loadedResources / totalResources) * 100)
    loadingProgress.textContent = `${progress}%`

    if (loadedResources === totalResources) {
        loadingScreen.classList.add('hidden')
        setTimeout(() => {
            loadingScreen.style.display = 'none'
        }, 500)
    }
}

let cat
const loader = new THREE.GLTFLoader()
loader.load(
    'assets/glb/oiiai.glb',
    function (gltf) {
        cat = gltf.scene
        cat.scale.set(4, 4, 4)
        scene.add(cat)
        const box = new THREE.Box3().setFromObject(cat)
        const center = box.getCenter(new THREE.Vector3())
        cat.position.sub(center)
        updateLoadingProgress()
    },
    // Add progress callback
    function (xhr) {
        const modelProgress = Math.round((xhr.loaded / xhr.total) * 100)
        loadingProgress.textContent = `${modelProgress}%`
    },
    // Add error callback
    function (error) {
        console.error('An error occurred loading the model:', error)
    }
)

// Effect states
let autoRotate = true
let rotationSpeed = 0.1
let currentEffect = 0
const effects = {
    particles: true,
    trails: false,
    geometric: false,
    explosion: false,
    morphing: true,
}

// Controls
const keyState = {}
document.addEventListener('keydown', (e) => {
    keyState[e.key] = true

    // Effect toggles
    if (e.key >= '1' && e.key <= '5') {
        const effectIndex = parseInt(e.key) - 1
        const effectKeys = Object.keys(effects)
        if (effectIndex < effectKeys.length) {
            effects[effectKeys[effectIndex]] = !effects[effectKeys[effectIndex]]
        }
    }

    // Rotation controls
    if (e.key === ' ') autoRotate = !autoRotate
    if (e.key === 'q') rotationSpeed = Math.max(0.05, rotationSpeed - 0.05)
    if (e.key === 'e') rotationSpeed = Math.min(0.5, rotationSpeed + 0.05)
    if (e.key === 'r') {
        camera.position.set(0, 0, 5)
        camera.lookAt(scene.position)
    }
})

document.addEventListener('keyup', (e) => {
    keyState[e.key] = false
})

function handleKeyboardInput() {
    const speed = 0.1
    if (keyState['ArrowLeft']) camera.position.x -= speed
    if (keyState['ArrowRight']) camera.position.x += speed
    if (keyState['ArrowUp']) camera.position.y += speed
    if (keyState['ArrowDown']) camera.position.y -= speed
    camera.lookAt(scene.position)
}

// Beat detection
function getBeatIntensity() {
    if (!analyser) return 0
    analyser.getByteFrequencyData(dataArray)
    const bass = dataArray.slice(0, 10).reduce((a, b) => a + b, 0) / 2550
    return bass
}

function updateParticles(beatIntensity) {
    const positions = particles.attributes.position.array
    const sizes = particles.attributes.size.array
    const colors = particles.attributes.color.array

    for (let i = 0; i < particleCount; i++) {
        const i3 = i * 3

        if (effects.particles) {
            positions[i3] +=
                Math.sin(Date.now() * 0.001 + i) * 0.002 * beatIntensity
            positions[i3 + 1] +=
                Math.cos(Date.now() * 0.001 + i) * 0.002 * beatIntensity
            positions[i3 + 2] +=
                Math.sin(Date.now() * 0.001 + i) * 0.002 * beatIntensity

            sizes[i] = Math.max(
                0.05,
                Math.min(
                    0.2,
                    sizes[i] + (Math.random() - 0.5) * 0.01 * beatIntensity
                )
            )

            if (Math.random() < 0.01) {
                colors[i3] = Math.random()
                colors[i3 + 1] = Math.random()
                colors[i3 + 2] = Math.random()
            }
        }

        const bound = 5
        if (Math.abs(positions[i3]) > bound) positions[i3] *= -0.9
        if (Math.abs(positions[i3 + 1]) > bound) positions[i3 + 1] *= -0.9
        if (Math.abs(positions[i3 + 2]) > bound) positions[i3 + 2] *= -0.9
    }

    particles.attributes.position.needsUpdate = true
    particles.attributes.size.needsUpdate = true
    particles.attributes.color.needsUpdate = true
}

function updateGeometricPatterns(beatIntensity) {
    if (!effects.geometric) return

    geometricPatterns.children.forEach((ring, i) => {
        ring.rotation.x += 0.01 * (i + 1) * beatIntensity
        ring.rotation.y += 0.02 * (i + 1) * beatIntensity
        ring.scale.setScalar(1 + beatIntensity * 0.2)
    })
}

function updateTrails(beatIntensity) {
    if (!effects.trails || !cat) return

    const positions = trails.geometry.attributes.position.array
    for (let i = trailCount - 1; i > 0; i--) {
        positions[i * 3] = positions[(i - 1) * 3]
        positions[i * 3 + 1] = positions[(i - 1) * 3 + 1]
        positions[i * 3 + 2] = positions[(i - 1) * 3 + 2]
    }

    positions[0] = cat.position.x
    positions[1] = cat.position.y
    positions[2] = cat.position.z

    trails.geometry.attributes.position.needsUpdate = true
    trails.material.opacity = Math.min(0.8, beatIntensity)
}

let time = 0
let isVibing = false
let animationFrameId

function animate() {
    if (!isVibing || document.hidden) {
        animationFrameId = requestAnimationFrame(animate)
        return
    }

    time += 0.02
    const beatIntensity = getBeatIntensity()

    if (cat && autoRotate) {
        cat.rotation.y += rotationSpeed * (1 + beatIntensity)
    }

    // Update effects
    updateParticles(beatIntensity)
    updateGeometricPatterns(beatIntensity)
    updateTrails(beatIntensity)

    // Update controls and handle keyboard input
    controls.update()
    handleKeyboardInput()

    // Animate lights
    lights.forEach((light, index) => {
        const angle = time * 2 + index * Math.PI * 0.3
        light.position.x = Math.cos(angle) * 8
        light.position.z = Math.sin(angle) * 8
        light.intensity = 4 + Math.sin(time * 8 + beatIntensity * Math.PI) * 2
    })

    // Dynamic camera movements based on beat
    if (effects.morphing) {
        camera.position.x += Math.sin(time * 2) * 0.01 * beatIntensity
        camera.position.y += Math.cos(time * 2) * 0.01 * beatIntensity
        camera.lookAt(scene.position)
    }

    // Particle explosions on strong beats
    if (effects.explosion && beatIntensity > 0.8) {
        createExplosion()
    }

    renderer.render(scene, camera)
    animationFrameId = requestAnimationFrame(animate)
}

// Particle explosion effect
function createExplosion() {
    const explosionCount = 50
    const explosionGeometry = new THREE.BufferGeometry()
    const explosionPositions = new Float32Array(explosionCount * 3)
    const explosionColors = new Float32Array(explosionCount * 3)

    for (let i = 0; i < explosionCount; i++) {
        const i3 = i * 3
        const angle = Math.random() * Math.PI * 2
        const radius = Math.random() * 2

        explosionPositions[i3] = Math.cos(angle) * radius
        explosionPositions[i3 + 1] = Math.sin(angle) * radius
        explosionPositions[i3 + 2] = (Math.random() - 0.5) * radius

        explosionColors[i3] = Math.random()
        explosionColors[i3 + 1] = Math.random()
        explosionColors[i3 + 2] = Math.random()
    }

    explosionGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(explosionPositions, 3)
    )
    explosionGeometry.setAttribute(
        'color',
        new THREE.BufferAttribute(explosionColors, 3)
    )

    const explosionMaterial = new THREE.PointsMaterial({
        size: 0.1,
        vertexColors: true,
        blending: THREE.AdditiveBlending,
        transparent: true,
        opacity: 0.8,
    })

    const explosion = new THREE.Points(explosionGeometry, explosionMaterial)
    explosion.position.copy(cat ? cat.position : new THREE.Vector3())
    scene.add(explosion)

    // Animate and remove explosion
    let scale = 1
    function animateExplosion() {
        scale += 0.1
        explosion.scale.setScalar(scale)
        explosion.material.opacity -= 0.02

        if (explosion.material.opacity > 0) {
            requestAnimationFrame(animateExplosion)
        } else {
            scene.remove(explosion)
            explosion.geometry.dispose()
            explosion.material.dispose()
        }
    }
    animateExplosion()
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        if (audio) audio.pause()
    } else if (isVibing) {
        if (audio) audio.play()
    }
})

document
    .getElementById('startButton')
    .addEventListener('click', function () {
        this.style.display = 'none'
        isVibing = true
        audio.play()
        setupAudioAnalysis()
    })

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
})

window.addEventListener('beforeunload', () => {
    isVibing = false
    cancelAnimationFrame(animationFrameId)
    if (audioContext) audioContext.close()
})

animate()