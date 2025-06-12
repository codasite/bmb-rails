import React, { useState, useEffect, useCallback, useRef } from 'react'
import {
  BracketListApi,
  BracketListRequest,
  BracketListResponse,
} from '../../brackets/shared/api/bracketListApi'
import { Spinner } from '../../brackets/shared/components/Spinner'
import { BracketClickEventHandler } from '../../brackets/shared/components/BracketClickEventHandler'

interface InfiniteScrollBracketListProps {
  initialStatus?: string
  initialTags?: string[]
  perPage?: number
}

export const InfiniteScrollBracketList: React.FC<
  InfiniteScrollBracketListProps
> = ({ initialStatus = 'live', initialTags = [], perPage = 10 }) => {
  const [brackets, setBrackets] = useState<string>('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [pagination, setPagination] = useState({
    currentPage: 0,
    hasMore: true,
    totalPages: 0,
    totalItems: 0,
    perPage: perPage,
  })
  const [currentStatus, setCurrentStatus] = useState(initialStatus)
  const [currentTags, setCurrentTags] = useState(initialTags)

  const bracketListApi = useRef(new BracketListApi())
  const containerRef = useRef<HTMLDivElement>(null)
  const isInitialLoad = useRef(true)

  const loadBrackets = useCallback(
    async (
      page: number = 1,
      reset: boolean = false,
      status: string = currentStatus,
      tags: string[] = currentTags
    ) => {
      if (loading) return

      setLoading(true)
      setError(null)

      try {
        const params: BracketListRequest = {
          page,
          perPage: perPage,
          status,
          tags,
        }

        const response: BracketListResponse =
          await bracketListApi.current.getBracketList(params)

        if (reset) {
          setBrackets(response.html)
        } else {
          setBrackets((prev) => prev + response.html)
        }

        setPagination(response.pagination)
      } catch (err) {
        console.error('Error loading brackets:', err)
        setError('Failed to load brackets. Please try again.')
      } finally {
        setLoading(false)
      }
    },
    [loading, perPage, currentStatus, currentTags]
  )

  // Handle filter changes
  const handleFilterChange = useCallback(
    (status: string, tags: string[] = []) => {
      setCurrentStatus(status)
      setCurrentTags(tags)
      setPagination((prev) => ({ ...prev, currentPage: 0, hasMore: true }))
      loadBrackets(1, true, status, tags)
    },
    [loadBrackets]
  )

  // Intersection Observer for infinite scroll
  const observerRef = useRef<IntersectionObserver>()
  const lastBracketElementRef = useCallback(
    (node: HTMLDivElement) => {
      if (loading) return
      if (observerRef.current) observerRef.current.disconnect()

      observerRef.current = new IntersectionObserver(
        (entries) => {
          if (entries[0].isIntersecting && pagination.hasMore) {
            loadBrackets(pagination.currentPage + 1, false)
          }
        },
        {
          rootMargin: '100px',
        }
      )

      if (node) observerRef.current.observe(node)
    },
    [loading, pagination.hasMore, pagination.currentPage, loadBrackets]
  )

  // Load initial brackets
  useEffect(() => {
    if (isInitialLoad.current) {
      isInitialLoad.current = false
      loadBrackets(1, true)
    }
  }, [loadBrackets])

  // Listen for filter button clicks (URL-based filtering)
  useEffect(() => {
    const handleUrlChange = () => {
      const urlParams = new URLSearchParams(window.location.search)
      const status = urlParams.get('status') || 'live'
      if (status !== currentStatus) {
        handleFilterChange(status)
      }
    }

    window.addEventListener('popstate', handleUrlChange)
    return () => window.removeEventListener('popstate', handleUrlChange)
  }, [currentStatus, handleFilterChange])

  return (
    <div className="tw-flex tw-flex-col tw-gap-15" ref={containerRef}>
      {/* Brackets Container */}
      <BracketClickEventHandler handlers={{}}>
        <div
          className="tw-flex tw-flex-col tw-gap-15"
          dangerouslySetInnerHTML={{ __html: brackets }}
        />
      </BracketClickEventHandler>

      {/* Loading Indicator */}
      {loading && (
        <div className="tw-flex tw-justify-center tw-py-30">
          <Spinner height={32} width={32} fill="white" />
        </div>
      )}

      {/* Error Message */}
      {error && (
        <div className="tw-text-center tw-py-30 tw-text-red-400">{error}</div>
      )}

      {/* Invisible element for intersection observer */}
      <div ref={lastBracketElementRef} className="tw-h-1" />
    </div>
  )
}

// Default export
export default InfiniteScrollBracketList
