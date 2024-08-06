import { H3 } from '../../../elements'

export const ProgressBarStep = (props: {
  step: number
  currentStep: number
  maxSteps: number
  stepName: string
}) => {
  const currentOrComplete = props.currentStep >= props.step
  const allComplete = props.currentStep === props.maxSteps - 1
  const showText = props.step === props.currentStep && !allComplete

  const stepClass = allComplete
    ? 'tw-bg-green'
    : currentOrComplete
    ? 'tw-bg-white'
    : 'tw-bg-white/20'

  const basis = 100 / props.maxSteps

  return (
    <div
      className="tw-flex tw-flex-col tw-items-center tw-gap-5"
      style={{ flex: `0 0 ${basis}%` }}
    >
      <div className={`tw-h-4 tw-w-full ${stepClass}`} />
      {showText && (
        <span className="tw-text-white tw-text-16 tw-font-500">
          {props.stepName}
        </span>
      )}
    </div>
  )
}
