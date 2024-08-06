import { useState } from 'react'
import { BracketRes } from '../../brackets/shared'
import { EntryFeePage } from './EntryFeePage'
import { SharePage } from './SharePage'
import { PageRouter } from './PageRouter'
import { Page } from './PageRouter/types'
import { GoLivePageProps } from './types'

const GoLivePage = (props: GoLivePageProps) => {
  const [chargesEnabled, setChargesEnabled] = useState(false)
  const pages: Page[] = [
    { title: 'Entry Fee', slug: 'entry-fee', Component: EntryFeePage },
    { title: 'Share', slug: 'share', Component: SharePage },
  ]
  return (
    <PageRouter
      pages={pages}
      chargesEnabled={chargesEnabled}
      setChargesEnabled={setChargesEnabled}
      basePathPart="go-live"
      {...props}
    />
  )
}

export default GoLivePage
