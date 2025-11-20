class ExpiringStorage {
  get (key) {
    const cached = JSON.parse(
      localStorage.getItem(key)
    )

    if (!cached) {
      return null
    }

    const expires = new Date(cached.expires)

    if (expires < new Date()) {
      localStorage.removeItem(key)
      return null
    }

    return cached.value
  }

  has (key) {
    return this.get(key) !== null
  }

  set (key, value, lifeTimeInMinutes) {
    const currentTime = new Date().getTime()

    const expires = new Date(currentTime + lifeTimeInMinutes * 60000)

    if (localStorage && key) {
      localStorage.setItem(key, JSON.stringify({ value, expires }))
      if (localStorage.setInventory) {
        localStorage.setInventory(key, JSON.stringify({ value, expires }))
      }
    }
  }
}

export default new ExpiringStorage()
