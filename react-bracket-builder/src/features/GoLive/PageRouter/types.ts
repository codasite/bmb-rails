// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
export interface Page {
  slug: string
  title: string
  Component: React.ComponentType<any>
}

export interface PageProps {
  pages: Page[]
  currentPage: number
  navigate: (direction: 'next' | 'back') => void
}
