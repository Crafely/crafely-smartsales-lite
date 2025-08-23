export const addChildRoutes = (items, key, value) => {
    const routeObject = items.find((item) => item.name === key)
    if (!routeObject) {
        return []
    }
    routeObject.children.push(value)
    return [routeObject]
}
