// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { FC, useEffect, useRef, useState } from 'react'
import { Page } from './types'
import { useLocation } from 'react-use'

interface PageRouterProps {
  pages: Page[]
  basePathPart: string // Everything after this is controlled by the router
  startPage?: number
}

export const PageRouter: FC<PageRouterProps & Record<string, any>> = (
  props
) => {
  const location = useLocation()
  const { pages, basePathPart } = props
  const [currentPage, setCurrentPage] = useState<number | null>(null)
  const [initialized, setInitialized] = useState(false)

  /**
   * Get the start page based on the startPage prop or the current path.
   * If the page is not found, use the first page.
   */
  const getStartPage = () => {
    return props.startPage || getPageIndexFromPath(location.pathname, pages)
  }

  /**
   * Initialize the page based on the current path, or the startPage if provided.
   * Update the history state to reflect the current page.
   */
  const initializePage = () => {
    const startPage = getStartPage()
    window.history.replaceState({}, '', getPageUrl(pages[startPage]))
    setCurrentPage(startPage)
    setInitialized(true)
  }

  /**
   * If this is the first render, initialize the page based on the current path.
   * Otherwise, update the current page based on the path.
   */
  useEffect(() => {
    if (!initialized) {
      initializePage()
    } else {
      const pageFromPath = getPageIndexFromPath(location.pathname, pages)
      setCurrentPage(pageFromPath)
    }
  }, [location])

  const navigate = (direction: 'next' | 'back') => {
    const newPage = direction === 'next' ? currentPage + 1 : currentPage - 1
    if (newPage >= 0 && newPage < pages.length) {
      window.history.pushState({}, '', getPageUrl(pages[newPage]))
    }
  }

  /**
   * Get the full path of the given page.
   * To be used with window.history.pushState or window.history.replaceState
   */
  const getPageUrl = (page: Page) => {
    const path = getBasePath(location.pathname, basePathPart) + page.slug
    return path
  }

  /**
   * Strips off everything after the basePathPart for the given pathname
   */
  const getBasePath = (pathname: string, basePathPart: string) => {
    const pathParts = getPathParts(pathname)

    const basePathIndex = pathParts.indexOf(basePathPart)
    if (basePathIndex !== -1) {
      return `/${pathParts.slice(0, basePathIndex + 1).join('/')}/`
    }
    return '/'
  }

  /**
   * Find the index of the page in the pages array based on the given path or 0 if not found.
   */
  const getPageIndexFromPath = (pathname: string, pages: Page[]) => {
    const pathParts = getPathParts(pathname)
    const lastPart = pathParts.pop()
    const page = pages.findIndex((page) => page.slug === lastPart)
    return page === -1 ? 0 : page
  }

  /**
   * Get the parts of the given path. Filters out empty strings.
   */
  const getPathParts = (pathname: string) => {
    return pathname.split('/').filter(Boolean)
  }

  if (initialized && currentPage !== null) {
    const PageComponent = pages[currentPage].Component
    return (
      <PageComponent {...props} navigate={navigate} currentPage={currentPage} />
    )
  }
  return null
}
