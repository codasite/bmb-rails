import Button from '../../ui/Button'
import { bracketApi } from '../../brackets/shared'
import { H4 } from '../../elements/typography/H4'
import { GoLivePageContainer } from './GoLivePageContainer'
import { useState } from 'react'
import { GoLiveSubPageProps } from './types'
import { logger } from '../../utils/Logger'
import Select from 'react-select'

interface BracketTypePageProps extends GoLiveSubPageProps {}

export const BracketTypePage = (props: BracketTypePageProps) => {
  const [loading, setLoading] = useState(false)
  const options = [
    { value: 'standard', label: 'Standard' },
    { value: 'voting', label: 'Voting' },
  ]
  const onContinue = async () => {
    setLoading(true)
    try {
      await bracketApi.updateBracket(props.bracket.id, { isVoting: true })
      props.navigate('next')
    } catch (error) {
      logger.error('Failed to update bracket', error)
    } finally {
      setLoading(false)
    }
  }
  return (
    <GoLivePageContainer
      title="Go Live!"
      subTitle={props.bracket.title}
      {...props}
    >
      <H4 className="tw-text-center tw-mb-30 md:tw-mb-60">
        Select Tournament Type
      </H4>
      <div className="tw-flex tw-flex-col tw-justify-center tw-items-center tw-pb-60">
        <Select
          options={options}
          defaultValue={options[0]}
          classNamePrefix="react-select"
          isSearchable={false}
        />
      </div>
      <Button
        disabled={loading}
        onClick={onContinue}
        className="tw-mt-15"
        variant="filled"
      >
        Next
      </Button>
    </GoLivePageContainer>
  )
}
